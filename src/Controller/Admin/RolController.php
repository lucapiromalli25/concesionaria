<?php

namespace App\Controller\Admin;

use App\Entity\Rol;
use App\Form\RolType;
use App\Repository\RolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/roles')]
#[IsGranted('roles.ver')]
class RolController extends AbstractController
{
    #[Route('/', name: 'app_roles_index', methods: ['GET'])]
    public function index(RolRepository $roles): Response
    {
        return $this->render('admin/roles/index.html.twig', [
            'filas' => $roles->findAllConUso(),
        ]);
    }

    #[Route('/new', name: 'app_roles_new', methods: ['GET', 'POST'])]
    #[IsGranted('roles.administrar')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $rol = new Rol();
        $form = $this->createForm(RolType::class, $rol, ['es_nuevo' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($rol);
            $em->flush();

            $this->addFlash('success', 'Rol creado. Ahora asignale permisos desde la matriz.');

            return $this->redirectToRoute('app_permisos_index');
        }

        return $this->render('admin/roles/_form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_roles_edit', methods: ['GET', 'POST'])]
    #[IsGranted('roles.administrar')]
    public function edit(Request $request, Rol $rol, EntityManagerInterface $em): Response
    {
        if ($rol->esAdministrador()) {
            $this->addFlash('error', 'El rol administrador no se puede editar.');

            return $this->redirectToRoute('app_roles_index');
        }

        $form = $this->createForm(RolType::class, $rol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Rol actualizado.');

            return $this->redirectToRoute('app_roles_index');
        }

        return $this->render('admin/roles/_form.html.twig', ['form' => $form->createView(), 'rol' => $rol]);
    }

    /**
     * Baja logica. No se permite si es de sistema o si alguien lo tiene asignado:
     * borrarlo dejaria a esa persona sin acceso sin que nadie se entere.
     */
    #[Route('/{id}/eliminar', name: 'app_roles_eliminar', methods: ['POST'])]
    #[IsGranted('roles.administrar')]
    public function eliminar(Request $request, Rol $rol, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isCsrfTokenValid('eliminar-rol'.$rol->getId(), (string) $request->request->get('token'))) {
            return new JsonResponse(['status' => 'error', 'message' => 'Token de seguridad invalido.'], Response::HTTP_BAD_REQUEST);
        }

        if ($rol->isEsSistema() || $rol->esAdministrador()) {
            return new JsonResponse(['status' => 'error', 'message' => 'El rol de sistema no se puede eliminar.'], Response::HTTP_BAD_REQUEST);
        }

        $usuarios = \count($rol->getUsuarioRoles());
        if ($usuarios > 0) {
            return new JsonResponse([
                'status' => 'error',
                'message' => sprintf('No se puede eliminar: %d usuario%s tiene%s este rol.', $usuarios, $usuarios === 1 ? '' : 's', $usuarios === 1 ? '' : 'n'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $rol->setDeletedAt(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse(['status' => 'success', 'redirect' => $this->generateUrl('app_roles_index')]);
    }
}
