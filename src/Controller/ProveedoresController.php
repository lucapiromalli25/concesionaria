<?php

namespace App\Controller;

use App\Entity\Proveedores;
use App\Form\ProveedoresType;
use App\Repository\ProveedoresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/proveedores')]
#[IsGranted('ROLE_MANAGER')] // Solo Gerentes y Admins gestionan proveedores
class ProveedoresController extends AbstractController
{
    #[Route('/', name: 'app_proveedores_index', methods: ['GET'])]
    public function index(ProveedoresRepository $proveedoresRepository): Response
    {
        return $this->render('proveedores/index.html.twig', [
            'proveedores' => $proveedoresRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_proveedores_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $proveedor = new Proveedores();
        $form = $this->createForm(ProveedoresType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($proveedor);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($proveedor, 'Proveedor creado correctamente.');
            }
            return $this->redirectToRoute('app_proveedores_index');
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }

        return $this->render('proveedores/_form_modal.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_proveedores_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Proveedores $proveedor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProveedoresType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($proveedor, 'Proveedor actualizado correctamente.');
            }
            return $this->redirectToRoute('app_proveedores_index');
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }
        
        return $this->render('proveedores/_form_modal.html.twig', ['form' => $form->createView()]);
    }

    private function getSuccessJsonResponse(Proveedores $proveedor, string $message): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success', 'message' => $message,
            'proveedor' => [
                'id' => $proveedor->getId(),
                'name' => $proveedor->getName(),
                'documentNumber' => $proveedor->getDocumentNumber(),
                'phone' => $proveedor->getPhone(),
                'displayText' => $proveedor->getName() // Para el modal anidado
            ]
        ]);
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) { $errors[$error->getOrigin()->getName()][] = $error->getMessage(); }
        foreach ($form as $child) { if (!$child->isValid()) { foreach ($child->getErrors(true) as $error) { $errors[$child->getName()][] = $error->getMessage(); } } }
        return $errors;
    }
}