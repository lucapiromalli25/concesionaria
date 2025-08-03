<?php

namespace App\Controller;

use App\Entity\Reservas;
use App\Entity\Vehiculos;
use App\Form\ReservaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ReservasRepository;

#[Route('/reservas')]
#[IsGranted('ROLE_SALESPERSON')]
class ReservaController extends AbstractController
{
    #[Route('/', name: 'app_reservas_index', methods: ['GET'])]
    public function index(ReservasRepository $reservasRepository): Response
    {
        return $this->render('reservas/index.html.twig', [
            'reservas' => $reservasRepository->findBy([], ['reservation_date' => 'DESC']),
        ]);
    }

    #[Route('/new/{id}', name: 'app_reservas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Vehiculos $vehiculo, EntityManagerInterface $entityManager): Response
    {
        // Verificación para no reservar un auto que no está en stock
        if ($vehiculo->getState() !== 'En Stock') {
            $this->addFlash('danger', 'Este vehículo no está disponible para ser reservado.');
            return $this->redirectToRoute('app_vehiculos_index');
        }

        $reserva = new Reservas();
        $reserva->setVehiculo($vehiculo); // Asocia el vehículo a la reserva
        $reserva->setStatus('Activa');   // Estado por defecto

        $form = $this->createForm(ReservaType::class, $reserva);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar el vendedor (usuario actual)
            $reserva->setVendedor($this->getUser());

            $reserva->setCreatedBy($this->getUser());
            $reserva->setCreatedAt(new \DateTimeImmutable());
            $reserva->setUpdatedeBy($this->getUser());
            $reserva->setUpdatedAt(new \DateTimeImmutable());
            
            // CAMBIAR EL ESTADO DEL VEHÍCULO
            $vehiculo->setState('Reservado');

            $entityManager->persist($reserva);
            $entityManager->flush();

            $this->addFlash('success', '¡Vehículo reservado con éxito!');
            return $this->redirectToRoute('app_vehiculos_index');
        }

        return $this->render('reservas/new.html.twig', [
            'vehiculo' => $vehiculo,
            'form' => $form->createView(),
        ]);
    }
}