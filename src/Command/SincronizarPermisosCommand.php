<?php

namespace App\Command;

use App\Entity\AuditableInterface;
use App\Entity\Funcionalidad;
use App\Repository\FuncionalidadRepository;
use App\Repository\RolRepository;
use App\Service\CatalogoPermisos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

/**
 * Vuelca config/permisos.php a la tabla `funcionalidad`.
 *
 * - Inserta las claves nuevas y les aplica la asignacion inicial a los roles.
 * - Actualiza nombre, descripcion, modulo y orden de las que ya existen.
 * - Las que estan en la base pero ya no en el archivo se marcan inactivas, no se
 *   borran: si se borraran, se perderia en silencio a que roles estaban asignadas.
 *
 * La asignacion a roles solo se toca al insertar. Lo que se cambie despues desde
 * el backoffice no se pisa.
 */
#[AsCommand(
    name: 'app:permisos:sincronizar',
    description: 'Sincroniza las funcionalidades de config/permisos.php con la base',
)]
class SincronizarPermisosCommand extends Command
{
    public function __construct(
        private readonly CatalogoPermisos $catalogo,
        private readonly FuncionalidadRepository $funcionalidades,
        private readonly RolRepository $roles,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('simular', null, InputOption::VALUE_NONE, 'Muestra los cambios sin guardarlos');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $simular = $input->getOption('simular');

        $existentes = $this->funcionalidades->findAllIndexadas();
        $rolesPorCodigo = [];
        foreach ($this->roles->findAll() as $rol) {
            $rolesPorCodigo[$rol->getCodigo()] = $rol;
        }

        $nuevas = $actualizadas = $desactivadas = 0;
        $orden = 0;
        $enElArchivo = [];

        foreach ($this->catalogo->porModulo() as $modulo => $funcionalidades) {
            foreach ($funcionalidades as $clave => $definicion) {
                $enElArchivo[] = $clave;
                ++$orden;

                $funcionalidad = $existentes[$clave] ?? null;

                if ($funcionalidad === null) {
                    $funcionalidad = (new Funcionalidad())->setClave($clave);
                    $this->em->persist($funcionalidad);
                    ++$nuevas;
                    $io->writeln("  <fg=green>+</> $clave");

                    foreach ($definicion['roles'] ?? [] as $codigoRol) {
                        if (isset($rolesPorCodigo[$codigoRol])) {
                            $rolesPorCodigo[$codigoRol]->addFuncionalidad($funcionalidad);
                        } else {
                            $io->warning("La clave $clave pide el rol '$codigoRol', que no existe.");
                        }
                    }
                } elseif ($this->cambio($funcionalidad, $modulo, $definicion, $orden)) {
                    ++$actualizadas;
                    $io->writeln("  <fg=yellow>~</> $clave");
                }

                $funcionalidad
                    ->setModulo($modulo)
                    ->setNombre($definicion['nombre'])
                    ->setDescripcion($definicion['descripcion'] ?? null)
                    ->setOrden($orden);

                // Una clave que vuelve al archivo se reactiva.
                if (!$funcionalidad->estaActivo()) {
                    $funcionalidad->setStatus(AuditableInterface::STATUS_ACTIVO)->setDeletedAt(null)->setDeletedBy(null);
                }
            }
        }

        foreach ($existentes as $clave => $funcionalidad) {
            if (!\in_array($clave, $enElArchivo, true) && $funcionalidad->estaActivo()) {
                $funcionalidad->setStatus(AuditableInterface::STATUS_INACTIVO)->setDeletedAt(new \DateTimeImmutable());
                ++$desactivadas;
                $io->writeln("  <fg=red>-</> $clave (ya no esta en el archivo)");
            }
        }

        if ($simular) {
            $io->note('Modo simulacion: no se guardo nada.');
        } else {
            $this->em->flush();
        }

        $io->success(sprintf(
            '%d nuevas, %d actualizadas, %d desactivadas. Total en el archivo: %d.',
            $nuevas, $actualizadas, $desactivadas, \count($enElArchivo)
        ));

        return Command::SUCCESS;
    }

    /** @param array{nombre: string, descripcion?: string} $definicion */
    private function cambio(Funcionalidad $funcionalidad, string $modulo, array $definicion, int $orden): bool
    {
        return $funcionalidad->getModulo() !== $modulo
            || $funcionalidad->getNombre() !== $definicion['nombre']
            || $funcionalidad->getDescripcion() !== ($definicion['descripcion'] ?? null)
            || $funcionalidad->getOrden() !== $orden;
    }
}
