<?php

namespace App\Controller;

use App\Entity\Vehiculos;
use App\Enum\VehicleStatus;
use App\Form\VehiculosType; // Asegúrate que el nombre es VehiculosType, no VehiculoType
use App\Repository\MarcasRepository;
use App\Repository\VehiculosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/vehiculos')]
#[IsGranted('vehiculos.ver')]
class VehiculosController extends AbstractController
{
    #[Route('/', name: 'app_vehiculos_index', methods: ['GET'])]
    public function index(Request $request, VehiculosRepository $vehiculosRepository, MarcasRepository $marcasRepository): Response
    {
        $filters = [
            'q'       => trim((string) $request->query->get('q')) ?: null,
            'estado'  => $request->query->get('estado') ?: null,
            'marca'   => $request->query->getInt('marca') ?: null,
            'anioMin' => $request->query->getInt('anioMin') ?: null,
            'anioMax' => $request->query->getInt('anioMax') ?: null,
        ];
        $sort = (string) $request->query->get('sort', 'id');
        $dir  = strtoupper((string) $request->query->get('dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $perPage    = max(10, min($request->query->getInt('perPage', 25), 100));
        $total      = $vehiculosRepository->countSearch($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($request->query->getInt('page', 1), $totalPages));

        return $this->render('vehiculos/index.html.twig', [
            'vehiculos'   => $vehiculosRepository->search($filters, $sort, $dir, $page, $perPage),
            'marcas'      => $marcasRepository->findBy([], ['name' => 'ASC']),
            'estados'     => array_column(VehicleStatus::cases(), 'value'),
            'counts'      => $vehiculosRepository->countByState(),
            'filters'     => $filters,
            'sort'        => $sort,
            'dir'         => $dir,
            'perPage'     => $perPage,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    #[Route('/new', name: 'app_vehiculos_new', methods: ['GET', 'POST'])]
    #[IsGranted('vehiculos.crear')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehiculo = new Vehiculos();
        $vehiculo->setState(VehicleStatus::EnStock->value);
        $form = $this->createForm(VehiculosType::class, $vehiculo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehiculo->setCreatedBy($this->getUser());
            $vehiculo->setCreatedAt(new \DateTimeImmutable());
            $vehiculo->setUpdatedBy($this->getUser());
            $vehiculo->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($vehiculo);
            $entityManager->flush();

            $this->addFlash('success', 'Vehículo creado correctamente.');
            return $this->redirectToRoute('app_vehiculos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vehiculos/form.html.twig', [
            'vehiculo'      => $vehiculo,
            'form'          => $form->createView(),
            'exchange_rate' => $this->getParameter('app.exchange_rate_usd_ars'),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vehiculos_edit', methods: ['GET', 'POST'])]
    #[IsGranted('vehiculos.editar')]
    public function edit(Request $request, Vehiculos $vehiculo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VehiculosType::class, $vehiculo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Lógica de auditoría para la actualización
            $vehiculo->setUpdatedBy($this->getUser());
            $vehiculo->setUpdatedAt(new \DateTimeImmutable());
            
            $entityManager->flush();

            $this->addFlash('success', 'Vehículo actualizado correctamente.');
            return $this->redirectToRoute('app_vehiculos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vehiculos/form.html.twig', [
            'vehiculo'      => $vehiculo,
            'form'          => $form->createView(),
            'exchange_rate' => $this->getParameter('app.exchange_rate_usd_ars'),
        ]);
    }

    #[Route('/{id}', name: 'app_vehiculos_show', methods: ['GET'])]
    public function show(Vehiculos $vehiculo): Response
    {
        return $this->render('vehiculos/show.html.twig', [
            'vehiculo' => $vehiculo,
        ]);
    }

}