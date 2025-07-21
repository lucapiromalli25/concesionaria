<?php

namespace App\Controller;

use App\Entity\Versiones;
use App\Form\VersionesType;
use App\Repository\VersionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/versiones')]
#[IsGranted('ROLE_ADMIN')]
class VersionesController extends AbstractController
{
    #[Route('/', name: 'app_versiones_index', methods: ['GET'])]
    public function index(VersionesRepository $versionesRepository): Response
    {
        return $this->render('versiones/index.html.twig', [
            'versiones' => $versionesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_versiones_new', methods: ['GET', 'POST'])]
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

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($versiones, 'Versión creada correctamente.');
            }
            return $this->redirectToRoute('app_versiones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('versiones/_form_modal.html.twig', [
            'versiones' => $versiones,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_versiones_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Versiones $versiones, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VersionesType::class, $versiones);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $versiones->setUpdatedBy($this->getUser());
            $versiones->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($versiones, 'Versión actualizada correctamente.');
            }
            return $this->redirectToRoute('app_versiones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('versiones/_form_modal.html.twig', [
            'versiones' => $versiones,
            'form' => $form->createView(),
        ]);
    }

    private function getSuccessJsonResponse(Versiones $version, string $message): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => $message,
            'version' => [
                'id' => $version->getId(),
                'name' => $version->getName(),
                'characteristics' => $version->getCharacteristics(),
                'modelo' => [
                    'name' => $version->getModelo()->getName(),
                    'marca' => ['name' => $version->getModelo()->getMarca()->getName()]
                ]
            ]
        ]);
    }
}