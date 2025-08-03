<?php

namespace App\Controller;

use App\Entity\Clientes;
use App\Form\ClienteType;
use App\Repository\ClientesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clientes')]
#[IsGranted('ROLE_SALESPERSON')]
class ClienteController extends AbstractController
{
    #[Route('/', name: 'app_clientes_index', methods: ['GET'])]
    public function index(ClientesRepository $clientesRepository): Response
    {
        return $this->render('clientes/index.html.twig', [
            'clientes' => $clientesRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_clientes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cliente = new Clientes();
        $form = $this->createForm(ClienteType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cliente->setCreatedBy($this->getUser()); 
            $cliente->setCreatedAt(new \DateTimeImmutable());
            $cliente->setUpdatedBy($this->getUser());
            $cliente->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($cliente);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($cliente, 'Cliente creado correctamente.');
            }
            return $this->redirectToRoute('app_clientes_index');
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }

        return $this->render('clientes/_form_modal.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_clientes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Clientes $cliente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClienteType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cliente->setUpdatedBy($this->getUser());
            $cliente->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($cliente, 'Cliente actualizado correctamente.');
            }
            return $this->redirectToRoute('app_clientes_index');
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }
        
        return $this->render('clientes/_form_modal.html.twig', ['form' => $form->createView()]);
    }

    private function getSuccessJsonResponse(Clientes $cliente, string $message): JsonResponse
    {
        $displayText = "{$cliente->getFirstName()} {$cliente->getLastName()} (DNI: {$cliente->getDocumentNumber()})";
        return new JsonResponse([
            'status' => 'success', 'message' => $message,
            'cliente' => [
                'id' => $cliente->getId(),
                'first_name' => $cliente->getFirstName(),
                'last_name' => $cliente->getLastName(),
                'document_number' => $cliente->getDocumentNumber(),
                'phone' => $cliente->getPhone(),
                'displayText' => $displayText
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