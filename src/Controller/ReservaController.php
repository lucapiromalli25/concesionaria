<?php

namespace App\Controller;

use App\Entity\Reservas;
use App\Entity\Vehiculos;
use App\Form\ReservaType;
use App\Repository\ReservasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;

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
        if ($vehiculo->getState() !== 'En Stock') {
            $this->addFlash('danger', 'Este vehículo no está disponible para ser reservado.');
            return $this->redirectToRoute('app_vehiculos_index');
        }
        
        $reserva = new Reservas();
        $reserva->setVehiculo($vehiculo);

        $form = $this->createForm(ReservaType::class, $reserva);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reserva->setVendedor($this->getUser());
            $reserva->setStatus('Activa');
            $vehiculo->setState('Reservado');
            
            $entityManager->persist($reserva);
            $entityManager->flush(); // 1. Guardamos para obtener el ID de la reserva

            // 2. Generamos y guardamos el número de recibo
            $reserva->setReceiptNumber('RR-' . str_pad($reserva->getId(), 6, '0', STR_PAD_LEFT));
            $entityManager->flush();

            $this->addFlash('success', '¡Reserva registrada con éxito!');
            return $this->redirectToRoute('app_reservas_success', ['id' => $reserva->getId()]);
        }

        return $this->render('reservas/new.html.twig', [
            'vehiculo' => $vehiculo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/success', name: 'app_reservas_success')]
    public function success(Reservas $reserva): Response
    {
        return $this->render('reservas/success.html.twig', [
            'reserva' => $reserva
        ]);
    }

    #[Route('/{id}/receipt', name: 'app_reservas_receipt')]
    public function receipt(Reservas $reserva): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('receipt/reserva_receipt_template.html.twig', [
            'reserva' => $reserva,
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'comprobante_reserva_' . $reserva->getReceiptNumber() . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);

        return new Response('', 200, ['Content-Type' => 'application/pdf']);
    }
}