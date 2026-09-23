<?php

namespace App\Controller;

use App\Entity\Proveedores;
use App\Form\ProveedoresType;
use App\Repository\ProveedoresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/proveedores')]
#[IsGranted('proveedores.ver')]
class ProveedoresController extends AbstractController
{
    #[Route('/', name: 'app_proveedores_index', methods: ['GET'])]
    public function index(Request $request, ProveedoresRepository $proveedoresRepository): Response
    {
        $q = trim((string) $request->query->get('q')) ?: null;

        $perPage    = 25;
        $total      = $proveedoresRepository->countSearch($q);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($request->query->getInt('page', 1), $totalPages));

        return $this->render('proveedores/index.html.twig', [
            'filas'       => $proveedoresRepository->search($q, $page, $perPage),
            'duplicados'  => $proveedoresRepository->duplicatedNames(),
            'q'           => $q,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    #[Route('/new', name: 'app_proveedores_new', methods: ['GET', 'POST'])]
    #[IsGranted('proveedores.crear')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $proveedor = new Proveedores();
        $form = $this->createForm(ProveedoresType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($proveedor);
            $entityManager->flush();

            $this->addFlash('success', 'Proveedor creado correctamente.');
            return $this->redirectToRoute('app_proveedores_index');
        }


        return $this->render('proveedores/_form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_proveedores_edit', methods: ['GET', 'POST'])]
    #[IsGranted('proveedores.editar')]
    public function edit(Request $request, Proveedores $proveedor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProveedoresType::class, $proveedor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Proveedor actualizado correctamente.');
            return $this->redirectToRoute('app_proveedores_index');
        }

        
        return $this->render('proveedores/_form.html.twig', ['form' => $form->createView()]);
    }


}