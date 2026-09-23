<?php

namespace App\Controller\Admin;

use App\Entity\AuditableInterface;
use App\Entity\Rol;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('usuarios.ver')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/usuarios/index.html.twig', [
            'filas'          => $userRepository->findAllWithActivity(),
            'dnisRepetidos'  => $userRepository->duplicatedDnis(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('usuarios.crear')]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['es_nuevo' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $this->sincronizarRoles($user, $form->get('rolesAsignados')->getData());

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Usuario creado correctamente.');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('admin/usuarios/_form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('usuarios.editar')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $this->sincronizarRoles($user, $form->get('rolesAsignados')->getData());
            $entityManager->flush();

            $this->addFlash('success', 'Usuario actualizado correctamente.');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('admin/usuarios/_form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Baja logica: el usuario no puede entrar mas, pero no se borra nada. Sus
     * ventas y reservas quedan intactas y sigue figurando como vendedor.
     */
    #[Route('/{id}/baja', name: 'app_user_baja', methods: ['POST'])]
    #[IsGranted('usuarios.eliminar')]
    public function baja(Request $request, User $user, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        if (!$this->isCsrfTokenValid('baja-usuario'.$user->getId(), (string) $request->request->get('token'))) {
            return new JsonResponse(['status' => 'error', 'message' => 'Token de seguridad invalido.'], Response::HTTP_BAD_REQUEST);
        }

        if ($user === $this->getUser()) {
            return new JsonResponse(['status' => 'error', 'message' => 'No podes darte de baja a vos mismo.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$user->estaActivo()) {
            return new JsonResponse(['status' => 'error', 'message' => 'El usuario ya estaba dado de baja.'], Response::HTTP_BAD_REQUEST);
        }

        // Si este era el ultimo administrador activo, nadie podria volver a entrar
        // a la administracion. Incluye el caso de que no sea administrador: ahi la
        // cuenta no cambia y la guarda no molesta.
        if ($userRepository->countAdministradoresActivos($user->getId()) === 0) {
            return new JsonResponse([
                'status'  => 'error',
                'message' => 'Es el ultimo administrador activo: si lo das de baja nadie puede administrar el sistema.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setDeletedAt(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse(['status' => 'success', 'redirect' => $this->generateUrl('app_user_index')]);
    }

    #[Route('/{id}/reactivar', name: 'app_user_reactivar', methods: ['POST'])]
    #[IsGranted('usuarios.eliminar')]
    public function reactivar(Request $request, User $user, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isCsrfTokenValid('reactivar-usuario'.$user->getId(), (string) $request->request->get('token'))) {
            return new JsonResponse(['status' => 'error', 'message' => 'Token de seguridad invalido.'], Response::HTTP_BAD_REQUEST);
        }

        if ($user->estaActivo()) {
            return new JsonResponse(['status' => 'error', 'message' => 'El usuario ya estaba activo.'], Response::HTTP_BAD_REQUEST);
        }

        // El listener solo sincroniza la baja; la vuelta se hace aca.
        $user->setDeletedAt(null);
        $user->setDeletedBy(null);
        $user->setStatus(AuditableInterface::STATUS_ACTIVO);
        $em->flush();

        return new JsonResponse(['status' => 'success', 'redirect' => $this->generateUrl('app_user_index')]);
    }

    /**
     * Deja al usuario exactamente con los roles tildados: agrega los nuevos y saca
     * los que se destildaron. La pivote la maneja la entidad.
     *
     * @param iterable<Rol> $seleccionados
     */
    private function sincronizarRoles(User $user, iterable $seleccionados): void
    {
        $nuevos = $seleccionados instanceof \Traversable ? iterator_to_array($seleccionados) : $seleccionados;

        foreach ($user->getRolesAsignados() as $actual) {
            if (!\in_array($actual, $nuevos, true)) {
                $user->removeRol($actual);
            }
        }

        foreach ($nuevos as $rol) {
            $user->addRol($rol);
        }
    }
}
