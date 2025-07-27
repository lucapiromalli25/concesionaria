<?php

namespace App\Controller;

use App\Entity\Modelos;
use App\Form\ModelosType;
use App\Repository\ModelosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/modelos')]
#[IsGranted('ROLE_ADMIN')]
class ModelosController extends AbstractController
{
    #[Route('/', name: 'app_modelos_index', methods: ['GET'])]
    public function index(ModelosRepository $modelosRepository): Response
    {
        return $this->render('modelos/index.html.twig', [
            'modelos' => $modelosRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_modelos_new', methods: ['GET', 'POST'])]
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

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Modelo creado correctamente.',
                    'modelo' => [
                        'id' => $modelo->getId(),
                        'name' => $modelo->getName(),
                        'marca' => ['name' => $modelo->getMarca()->getName()]
                    ]
                ]);
            }
            return $this->redirectToRoute('app_modelos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('modelos/_form_modal.html.twig', [
            'modelo' => $modelo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_modelos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Modelos $modelo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModelosType::class, $modelo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $modelo->setUpdatedBy($this->getUser());
            $modelo->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Modelo actualizado correctamente.',
                    'modelo' => [
                        'id' => $modelo->getId(),
                        'name' => $modelo->getName(),
                        'marca' => ['name' => $modelo->getMarca()->getName()]
                    ]
                ]);
            }
            return $this->redirectToRoute('app_modelos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('modelos/_form_modal.html.twig', [
            'modelo' => $modelo,
            'form' => $form->createView(),
        ]);
    }
}