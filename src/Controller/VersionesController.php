<?php

namespace App\Controller;

use App\Entity\Versiones;
use App\Form\VersionesType;
use App\Repository\VersionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/versiones')]
#[IsGranted('catalogo.ver')]
class VersionesController extends AbstractController
{
    #[Route('/', name: 'app_versiones_index', methods: ['GET'])]
    public function index(Request $request, VersionesRepository $versionesRepository): Response
    {
        $q   = trim((string) $request->query->get('q')) ?: null;
        $uso = $request->query->get('uso') ?: null;

        $perPage    = 25;
        $total      = $versionesRepository->countSearch($q, $uso);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($request->query->getInt('page', 1), $totalPages));

        return $this->render('versiones/index.html.twig', [
            'q'           => $q,
            'uso'         => $uso,
            'filas'       => $versionesRepository->searchWithUsage($q, $uso, $page, $perPage),
            'resumen'     => $versionesRepository->usageSummary(),
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    #[Route('/new', name: 'app_versiones_new', methods: ['GET', 'POST'])]
    #[IsGranted('catalogo.crear')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $versiones = new Versiones();
        $form = $this->createForm(VersionesType::class, $versiones);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $versiones->setCreatedBy($this->getUser());
            $versiones->setCreatedAt(new \DateTimeImmutable());
            $versiones->setUpdatedBy($this->getUser());
            $versiones->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($versiones);
            $entityManager->flush();

            return $this->redirectToRoute('app_versiones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('versiones/_form.html.twig', [
            'versiones' => $versiones,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_versiones_edit', methods: ['GET', 'POST'])]
    #[IsGranted('catalogo.editar')]
    public function edit(Request $request, Versiones $versiones, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VersionesType::class, $versiones);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $versiones->setUpdatedBy($this->getUser());
            $versiones->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_versiones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('versiones/_form.html.twig', [
            'versiones' => $versiones,
            'form' => $form->createView(),
        ]);
    }


}