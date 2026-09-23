<?php

namespace App\Controller;

use App\Entity\Modelos;
use App\Form\ModelosType;
use App\Repository\MarcasRepository;
use App\Repository\ModelosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface; // <-- Añade este import
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/modelos')]
#[IsGranted('catalogo.ver')]
class ModelosController extends AbstractController
{
    #[Route('/', name: 'app_modelos_index', methods: ['GET'])]
    public function index(Request $request, ModelosRepository $modelosRepository, MarcasRepository $marcasRepository): Response
    {
        $q       = trim((string) $request->query->get('q')) ?: null;
        $marcaId = (int) $request->query->get('marca') ?: null;

        $perPage    = 25;
        $total      = $modelosRepository->countSearch($q, $marcaId);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min((int) $request->query->get('page', 1), $totalPages));

        return $this->render('modelos/index.html.twig', [
            'q'           => $q,
            'marcaId'     => $marcaId,
            'marcas'      => $marcasRepository->findBy([], ['name' => 'ASC']),
            'filas'       => $modelosRepository->searchWithUsage($q, $marcaId, $page, $perPage),
            'duplicados'  => $modelosRepository->duplicatedNames(),
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    #[Route('/new', name: 'app_modelos_new', methods: ['GET', 'POST'])]
    #[IsGranted('catalogo.crear')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $modelo = new Modelos();
        $form = $this->createForm(ModelosType::class, $modelo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $modelo->setCreatedBy($this->getUser());
            $modelo->setCreatedAt(new \DateTimeImmutable());
            $modelo->setUpdatedBy($this->getUser());
            $modelo->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($modelo);
            $entityManager->flush();

            return $this->redirectToRoute('app_modelos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('modelos/_form.html.twig', [
            'modelo' => $modelo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_modelos_edit', methods: ['GET', 'POST'])]
    #[IsGranted('catalogo.editar')]
    public function edit(Request $request, Modelos $modelo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModelosType::class, $modelo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $modelo->setUpdatedBy($this->getUser());
            $modelo->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_modelos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('modelos/_form.html.twig', [
            'modelo' => $modelo,
            'form' => $form->createView(),
        ]);
    }
}