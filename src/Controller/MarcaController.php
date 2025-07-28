<?php

namespace App\Controller;

use App\Entity\Marcas;
use App\Form\MarcaType;
use App\Repository\MarcasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface; // <-- Importante
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/marcas')]
#[IsGranted('ROLE_ADMIN')]
class MarcaController extends AbstractController
{
    #[Route('/', name: 'app_marca_index', methods: ['GET'])]
    public function index(MarcasRepository $marcaRepository): Response
    {
        return $this->render('marca/index.html.twig', [
            'marcas' => $marcaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_marca_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $marca = new Marcas();
        $form = $this->createForm(MarcaType::class, $marca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $marca->setCreatedBy($this->getUser()); 
            $marca->setCreatedAt(new \DateTimeImmutable());
            $marca->setUpdatedBy($this->getUser());
            $marca->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($marca);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($marca, 'Marca creada correctamente.');
            }
            
            $this->addFlash('success', 'Marca creada correctamente.');
            return $this->redirectToRoute('app_marca_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'El formulario contiene errores.',
                'errors' => $this->getFormErrors($form)
            ], Response::HTTP_BAD_REQUEST);
        }
        
        return $this->render('marca/_form_modal.html.twig', [
            'marca' => $marca,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_marca_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Marcas $marca, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MarcaType::class, $marca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $marca->setUpdatedBy($this->getUser());
            $marca->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($marca, 'Marca actualizada correctamente.');
            }

            $this->addFlash('success', 'Marca actualizada correctamente.');
            return $this->redirectToRoute('app_marca_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'El formulario contiene errores.',
                'errors' => $this->getFormErrors($form)
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->render('marca/_form_modal.html.twig', [
            'marca' => $marca,
            'form' => $form->createView(),
        ]);
    }

    private function getSuccessJsonResponse(Marcas $marca, string $message): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => $message,
            'marca' => [
                'id' => $marca->getId(),
                'name' => $marca->getName(),
                'displayText' => $marca->getName() // <-- CAMPO AÑADIDO
            ]
        ]);
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()][] = $error->getMessage();
        }
        foreach ($form as $child) {
            if (!$child->isValid()) {
                foreach ($child->getErrors(true) as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }
        return $errors;
    }
}