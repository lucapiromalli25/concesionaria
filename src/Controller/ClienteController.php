<?php

namespace App\Controller;

use App\Entity\Clientes;
use App\Form\ClienteType;
use App\Repository\ClientesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clientes')]
#[IsGranted('clientes.ver')]
class ClienteController extends AbstractController
{
    #[Route('/', name: 'app_clientes_index', methods: ['GET'])]
    public function index(Request $request, ClientesRepository $clientesRepository): Response
    {
        $q    = trim((string) $request->query->get('q')) ?: null;
        $sort = (string) $request->query->get('sort', 'apellido');
        $dir  = strtoupper((string) $request->query->get('dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $perPage    = 25;
        $total      = $clientesRepository->countSearch($q);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($request->query->getInt('page', 1), $totalPages));

        return $this->render('clientes/index.html.twig', [
            'filas'       => $clientesRepository->search($q, $sort, $dir, $page, $perPage),
            'q'           => $q,
            'sort'        => $sort,
            'dir'         => $dir,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    #[Route('/new', name: 'app_clientes_new', methods: ['GET', 'POST'])]
    #[IsGranted('clientes.crear')]
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

            $this->addFlash('success', 'Cliente creado correctamente.');
            return $this->redirectToRoute('app_clientes_index');
        }

        return $this->render('clientes/_form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_clientes_edit', methods: ['GET', 'POST'])]
    #[IsGranted('clientes.editar')]
    public function edit(Request $request, Clientes $cliente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClienteType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cliente->setUpdatedBy($this->getUser());
            $cliente->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Cliente actualizado correctamente.');
            return $this->redirectToRoute('app_clientes_index');
        }

        return $this->render('clientes/_form.html.twig', ['form' => $form->createView()]);
    }


}