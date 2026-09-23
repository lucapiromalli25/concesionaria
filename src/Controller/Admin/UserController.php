<?php

namespace App\Controller\Admin;

use App\Entity\Rol;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
