<?php

namespace App\Controller\Admin;

use App\Entity\Rol;
use App\Repository\FuncionalidadRepository;
use App\Repository\RolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Matriz funcionalidad x rol. Las funcionalidades no se crean ni se borran aca:
 * salen de config/permisos.php via `app:permisos:sincronizar`. Lo unico editable
 * es que rol tiene cada una.
 */
#[Route('/admin/permisos')]
#[IsGranted('roles.ver')]
class PermisoController extends AbstractController
{
    #[Route('/', name: 'app_permisos_index', methods: ['GET'])]
    public function index(RolRepository $roles, FuncionalidadRepository $funcionalidades): Response
    {
        $editables = array_filter($roles->findAll(), fn (Rol $rol) => $rol->estaActivo() && !$rol->esSuperadmin());
        usort($editables, fn (Rol $a, Rol $b) => strcmp((string) $a->getNombre(), (string) $b->getNombre()));

        // rol_id => [clave => true], para marcar los checkboxes sin recorrer la
        // coleccion dentro del doble bucle del template.
        $marcados = [];
        foreach ($editables as $rol) {
            $marcados[$rol->getId()] = array_fill_keys($rol->getClaves(), true);
        }

        return $this->render('admin/permisos/index.html.twig', [
            'roles'      => $editables,
            'porModulo'  => $funcionalidades->findAgrupadasPorModulo(),
            'marcados'   => $marcados,
            'superadmin' => $roles->findOneByCodigo(Rol::CODIGO_SUPERADMIN),
        ]);
    }

    #[Route('/guardar', name: 'app_permisos_guardar', methods: ['POST'])]
    #[IsGranted('roles.administrar')]
    public function guardar(Request $request, RolRepository $roles, FuncionalidadRepository $funcionalidades, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('permisos', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var array<int|string, list<string>> $enviados rol_id => [funcionalidad_id] */
        $enviados = $request->request->all('permisos');
        $porClave = $funcionalidades->findAllIndexadas();
        $porId = [];
        foreach ($porClave as $funcionalidad) {
            $porId[$funcionalidad->getId()] = $funcionalidad;
        }

        $cambios = 0;

        foreach ($roles->findAll() as $rol) {
            if (!$rol->estaActivo() || $rol->esSuperadmin()) {
                continue;
            }

            // Un rol sin ninguna casilla tildada no llega en el POST: eso significa
            // "sin permisos", no "no lo toques".
            $idsElegidos = array_map('intval', $enviados[$rol->getId()] ?? []);

            foreach ($porId as $id => $funcionalidad) {
                $tenia = $rol->tieneFuncionalidad($funcionalidad);
                $tiene = \in_array($id, $idsElegidos, true);

                if ($tenia === $tiene) {
                    continue;
                }

                $tiene ? $rol->addFuncionalidad($funcionalidad) : $rol->removeFuncionalidad($funcionalidad);
                ++$cambios;
            }
        }

        $em->flush();

        $this->addFlash('success', $cambios === 0
            ? 'No hubo cambios que guardar.'
            : sprintf('%d permiso%s actualizado%s.', $cambios, $cambios === 1 ? '' : 's', $cambios === 1 ? '' : 's'));

        return $this->redirectToRoute('app_permisos_index');
    }
}
