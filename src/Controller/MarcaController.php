<?php

namespace App\Controller;

use App\Entity\Marcas;
use App\Form\MarcaType;
use App\Repository\MarcasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface; // <-- Importante
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/marcas')]
#[IsGranted('catalogo.ver')]
class MarcaController extends AbstractController
{
    #[Route('/', name: 'app_marca_index', methods: ['GET'])]
    public function index(Request $request, MarcasRepository $marcaRepository): Response
    {
        $q = trim((string) $request->query->get('q')) ?: null;

        return $this->render('marca/index.html.twig', [
            'q'     => $q,
            'filas' => $marcaRepository->searchWithUsage($q),
        ]);
    }

    #[Route('/new', name: 'app_marca_new', methods: ['GET', 'POST'])]
    #[IsGranted('catalogo.crear')]
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

            
            $this->addFlash('success', 'Marca creada correctamente.');
            return $this->redirectToRoute('app_marca_index', [], Response::HTTP_SEE_OTHER);
        }

        
        return $this->render('marca/_form.html.twig', [
            'marca' => $marca,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_marca_edit', methods: ['GET', 'POST'])]
    #[IsGranted('catalogo.editar')]
    public function edit(Request $request, Marcas $marca, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MarcaType::class, $marca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $marca->setUpdatedBy($this->getUser());
            $marca->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();


            $this->addFlash('success', 'Marca actualizada correctamente.');
            return $this->redirectToRoute('app_marca_index', [], Response::HTTP_SEE_OTHER);
        }


        return $this->render('marca/_form.html.twig', [
            'marca' => $marca,
            'form' => $form->createView(),
        ]);
    }


}