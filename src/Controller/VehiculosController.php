<?php

namespace App\Controller;

use App\Entity\Vehiculos;
use App\Form\VehiculosType; // Asegúrate que el nombre es VehiculosType, no VehiculoType
use App\Repository\VehiculosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/vehiculos')]
#[IsGranted('ROLE_MANAGER')]
class VehiculosController extends AbstractController
{
    #[Route('/', name: 'app_vehiculos_index', methods: ['GET'])]
    public function index(VehiculosRepository $vehiculosRepository): Response
    {
        return $this->render('vehiculos/index.html.twig', [
            'vehiculos' => $vehiculosRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vehiculos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehiculo = new Vehiculos();
        $form = $this->createForm(VehiculosType::class, $vehiculo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehiculo->setCreatedBy($this->getUser());
            $vehiculo->setCreatedAt(new \DateTimeImmutable());
            $vehiculo->setUpdatedBy($this->getUser());
            $vehiculo->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($vehiculo);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($vehiculo, 'Vehículo creado correctamente.');
            }
            $this->addFlash('success', 'Vehículo creado correctamente.');
            return $this->redirectToRoute('app_vehiculos_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'message' => 'El formulario contiene errores.', 'errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }

        return $this->render('vehiculos/_form_modal.html.twig', ['vehiculo' => $vehiculo, 'form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_vehiculos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vehiculos $vehiculo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VehiculosType::class, $vehiculo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Lógica de auditoría para la actualización
            $vehiculo->setUpdatedBy($this->getUser());
            $vehiculo->setUpdatedAt(new \DateTimeImmutable());
            
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->getSuccessJsonResponse($vehiculo, 'Vehículo actualizado correctamente.');
            }
            $this->addFlash('success', 'Vehículo actualizado correctamente.');
            return $this->redirectToRoute('app_vehiculos_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'message' => 'El formulario contiene errores.', 'errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }

        return $this->render('vehiculos/_form_modal.html.twig', ['vehiculo' => $vehiculo, 'form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'app_vehiculos_show', methods: ['GET'])]
    public function show(Vehiculos $vehiculo): Response
    {
        // Gracias al ParamConverter de Symfony, ya tenemos el objeto Vehiculo
        // correcto a partir del {id} de la URL. Solo tenemos que renderizar la vista.
        return $this->render('vehiculos/show.html.twig', [
            'vehiculo' => $vehiculo,
        ]);
    }

    private function getSuccessJsonResponse(Vehiculos $vehiculo, string $message): JsonResponse
    {
        $version = $vehiculo->getVersion();
        $modelo = $version->getModelo();
        $marca = $modelo->getMarca();

        return new JsonResponse([
            'status' => 'success',
            'message' => $message,
            'vehiculo' => [
                'id' => $vehiculo->getId(),
                'anio' => $vehiculo->getAnio(),
                'color' => $vehiculo->getColor(),
                'precio_venta_sugerido' => $vehiculo->getSuggestedRetailPrice(),
                'version' => ['name' => $version->getName()],
                'modelo' => ['name' => $modelo->getName()],
                'marca' => ['name' => $marca->getName()]
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