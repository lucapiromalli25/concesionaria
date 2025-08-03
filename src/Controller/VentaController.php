<?php

namespace App\Controller;

use App\Entity\Vehiculos;
use App\Entity\Ventas;
use App\Form\VentaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\VentasRepository;

#[Route('/ventas')]
#[IsGranted('ROLE_SALESPERSON')]
class VentaController extends AbstractController
{
    #[Route('/', name: 'app_ventas_index', methods: ['GET'])]
    public function index(VentasRepository $ventasRepository): Response
    {
        return $this->render('ventas/index.html.twig', [
            // Buscamos todas las ventas, ordenadas por fecha descendente
            'ventas' => $ventasRepository->findBy([], ['sale_date' => 'DESC']),
        ]);
    }

    #[Route('/new/{id}', name: 'app_ventas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Vehiculos $vehiculo, EntityManagerInterface $entityManager): Response
    {
        // Verificación para no vender un auto ya vendido
        if (!in_array($vehiculo->getState(), ['En Stock', 'Reservado'])) {
            $this->addFlash('danger', 'Este vehículo no está disponible para la venta.');
            return $this->redirectToRoute('app_vehiculos_index');
        }

        $venta = new Ventas();
        $venta->setVehiculo($vehiculo); // Asocia el vehículo a la venta

        // 2. (MEJORA UX) SI EL AUTO ESTÁ RESERVADO, PRE-SELECCIONAMOS AL CLIENTE
        if ($reserva = $vehiculo->getReserva()) {
            $venta->setCliente($reserva->getCliente());
            $venta->setFinalSalePrice($vehiculo->getSuggestedRetailPrice());
        }

        $form = $this->createForm(VentaType::class, $venta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar el vendedor (usuario actual)
            $venta->setVendedor($this->getUser());
            
            // CAMBIAR EL ESTADO DEL VEHÍCULO
            $vehiculo->setState('Vendido');

            if ($reserva = $vehiculo->getReserva()) {
                $reserva->setStatus('Completada');
            }

            $venta->setCreatedBy($this->getUser());
            $venta->setCreatedAt(new \DateTimeImmutable());
            $venta->setUpdatedBy($this->getUser());
            $venta->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($venta);
            // No hace falta persistir el vehículo, Doctrine ya lo está observando
            $entityManager->flush();

            $this->addFlash('success', '¡Venta registrada con éxito!');
            return $this->redirectToRoute('app_vehiculos_index');
        }

        return $this->render('ventas/new.html.twig', [
            'vehiculo' => $vehiculo,
            'form' => $form->createView(),
        ]);
    }
}