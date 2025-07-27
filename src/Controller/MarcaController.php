<?php

namespace App\Controller;

use App\Entity\Marcas;
use App\Form\MarcaType;
use App\Repository\MarcasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        // Si la petición es AJAX y el formulario es valido
        if ($form->isSubmitted() && $form->isValid()) {
            // Aquí iría la lógica de auditoría
            $marca->setCreatedBy($this->getUser()); 
            $marca->setCreatedAt(new \DateTimeImmutable());
            $marca->setUpdatedBy($this->getUser());
            $marca->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($marca);
            $entityManager->flush();

            // Si es una peticion AJAX, devuelvo un JSON
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Marca creada correctamente.',
                    'marca' => [
                        'id' => $marca->getId(),
                        'name' => $marca->getName()
                    ]
                ]);
            }
            
            // Si no es AJAX, hago la redirección tradicional
            $this->addFlash('success', 'Marca creada correctamente.');
            return $this->redirectToRoute('app_marca_index', [], Response::HTTP_SEE_OTHER);
        }

        // Si es AJAX y el formulario no es valido
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $errors = [];
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse([
                'status' => 'error',
                'message' => 'El formulario contiene errores.',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Carga inicial del formulario
        return $this->render('marca/_form_modal.html.twig', [
            'marca' => $marca,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_marca_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Marcas $marca, EntityManagerInterface $entityManager): Response
    {
        // El ParamConverter de Symfony ya trae la entidad 'Marcas' correcta usando el {id} de la URL.
        $form = $this->createForm(MarcaType::class, $marca);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $marca->setUpdatedBy($this->getUser());
            $marca->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Marca actualizada correctamente.',
                    'marca' => [
                        'id' => $marca->getId(),
                        'name' => $marca->getName()
                    ]
                ]);
            }

            $this->addFlash('success', 'Marca actualizada correctamente.');
            return $this->redirectToRoute('app_marca_index', [], Response::HTTP_SEE_OTHER);
        }

        // Si es AJAX y el formulario no es valido
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $errors = [];
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse([
                'status' => 'error',
                'message' => 'El formulario contiene errores.',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        // Carga inicial del formulario con los datos de la marca para editar
        return $this->render('marca/_form_modal.html.twig', [
            'marca' => $marca,
            'form' => $form->createView(),
        ]);
    }
    
}