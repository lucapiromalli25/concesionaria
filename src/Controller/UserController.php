<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')] // Solo los administradores pueden gestionar usuarios
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response|JsonResponse
{
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            // Hashear la contraseña si se proporcionó una
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->persist($user);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($user, 'Usuario creado correctamente.');
            }

            return $this->redirectToRoute('app_user_index');
        }

        // ❌ formulario inválido
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => 'error',
                'errors' => $this->getFormErrors($form),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    // 🟢 solo renderizar HTML si es request normal (no AJAX)
    if ($request->isXmlHttpRequest()) {
        return new JsonResponse([
            'status' => 'error',
            'errors' => ['global' => ['El formulario no se procesó correctamente.']]
        ], Response::HTTP_BAD_REQUEST);
    }

    return $this->render('user/_form_modal.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hashear la contraseña solo si el campo no está vacío
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }
            
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($user, 'Usuario actualizado correctamente.');
            }
            return $this->redirectToRoute('app_user_index');
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }
        
        return $this->render('user/_form_modal.html.twig', ['form' => $form->createView()]);
    }

    private function getSuccessJsonResponse(User $user, string $message): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success', 'message' => $message,
            'user' => [
                'id' => $user->getId(),
                'complete_name' => $user->getCompleteName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        // Recorrer todos los errores del formulario, incluyendo los hijos
        foreach ($form->getErrors(true) as $error) {
            // $error ahora es siempre un FormError
            $origin = $error->getOrigin();
            $name = $origin instanceof FormInterface ? $origin->getName() : 'global';
            $errors[$name][] = $error->getMessage();
        }

        return $errors;
    }

}