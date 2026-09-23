<?php

namespace App\Security;

use App\Entity\User;
use App\Service\CatalogoPermisos;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Resuelve los permisos con formato `modulo.accion` contra los roles del usuario.
 *
 * Reglas:
 * - El rol superadmin pasa siempre, por codigo. Sin esto, sacarse el permiso de
 *   administrar roles dejaria el sistema sin forma de volver a entrar. El rol
 *   administrador ya no pasa por codigo: tiene sus permisos en la tabla, como
 *   cualquier otro, y no llega al modulo de Administracion.
 * - Una clave que no esta en config/permisos.php se rechaza y se loguea: es un
 *   error de programacion (un IsGranted con un typo), no un permiso denegado.
 * - Sin usuario, no pasa nada.
 */
class PermisoVoter extends Voter
{
    /** Claves del usuario de la request actual, para no recorrer los roles en cada chequeo. */
    private ?string $usuarioCacheado = null;

    /** @var list<string> */
    private array $clavesCacheadas = [];

    public function __construct(
        private readonly CatalogoPermisos $catalogo,
        private readonly LoggerInterface $logger,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return (bool) preg_match('/^[a-z_]+\.[a-z_]+$/', $attribute);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $usuario = $token->getUser();

        if (!$usuario instanceof User) {
            return false;
        }

        if (!$this->catalogo->existe($attribute)) {
            $this->logger->error('Permiso inexistente: "{clave}". Falta declararla en config/permisos.php.', ['clave' => $attribute]);

            return false;
        }

        if ($usuario->esSuperadmin()) {
            return true;
        }

        return \in_array($attribute, $this->clavesDe($usuario), true);
    }

    /** @return list<string> */
    private function clavesDe(User $usuario): array
    {
        $identificador = $usuario->getUserIdentifier();

        if ($this->usuarioCacheado !== $identificador) {
            $this->usuarioCacheado = $identificador;
            $this->clavesCacheadas = $usuario->getClaves();
        }

        return $this->clavesCacheadas;
    }
}
