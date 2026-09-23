<?php

namespace App\Controller;

use App\Entity\Reservas;
use App\Entity\Vehiculos;
use App\Enum\VehicleStatus;
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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/reservas')]
#[IsGranted('reservas.ver')]
class ReservaController extends AbstractController
{
    #[Route('/', name: 'app_reservas_index', methods: ['GET'])]
    public function index(Request $request, ReservasRepository $reservasRepository): Response
    {
        $q      = trim((string) $request->query->get('q')) ?: null;
        $estado = $request->query->get('estado') ?: null;

        $perPage    = 25;
        $total      = $reservasRepository->countSearch($q, $estado);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min((int) $request->query->get('page', 1), $totalPages));

        return $this->render('reservas/index.html.twig', [
            'reservas'    => $reservasRepository->search($q, $estado, $page, $perPage),
            'resumen'     => $reservasRepository->summary(),
            'q'           => $q,
            'estado'      => $estado,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    #[Route('/new/{id}', name: 'app_reservas_new', methods: ['GET', 'POST'])]
    #[IsGranted('reservas.crear')]
    public function new(Request $request, Vehiculos $vehiculo, EntityManagerInterface $entityManager): Response
    {
        if ($vehiculo->getState() !== VehicleStatus::EnStock->value) {
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
            $vehiculo->setState(VehicleStatus::Reservado->value);
            
            $entityManager->persist($reserva);
            $entityManager->flush(); // 1. Guardamos para obtener el ID de la reserva

            // 2. Generamos y guardamos el número de recibo
            $reserva->setReceiptNumber('RR-' . str_pad($reserva->getId(), 6, '0', STR_PAD_LEFT));
            $entityManager->flush();

            $this->saveReceiptAsPdf($reserva);

            $this->addFlash('success', '¡Reserva registrada con éxito!');
            return $this->redirectToRoute('app_reservas_success', ['id' => $reserva->getId()]);
        }

        return $this->render('reservas/new.html.twig', [
            'vehiculo' => $vehiculo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservas_edit', methods: ['GET', 'POST'])]
    #[IsGranted('reservas.editar')]
    public function edit(Request $request, Reservas $reserva, EntityManagerInterface $entityManager): Response
    {
        $previousStatus = $reserva->getStatus();
        $form = $this->createForm(ReservaType::class, $reserva, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($reserva->getStatus() === 'Cancelada' && $previousStatus !== 'Cancelada') {
                $reserva->getVehiculo()->setState(VehicleStatus::EnStock->value);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Reserva actualizada correctamente.');
            return $this->redirectToRoute('app_reservas_index');
        }

        return $this->render('reservas/edit.html.twig', [
            'reserva' => $reserva,
            'form'    => $form->createView(),
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
    #[IsGranted('reservas.ver_recibo')]
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

    #[Route('/{id}/view-receipt', name: 'app_reservas_view_pdf', methods: ['GET'])]
    #[IsGranted('reservas.ver_recibo')]
    public function viewStoredPdf(Reservas $reserva): Response
    {
        $filename = 'comprobante_reserva_' . $reserva->getReceiptNumber() . '.pdf';
        $directory = $this->getParameter('reservations_directory'); 
        $filepath = $directory . '/' . $filename;

        if (!file_exists($filepath)) {
            // Opción A: Si no existe el archivo, podrías redirigir a la generación dinámica
            return $this->redirectToRoute('app_reservas_receipt', ['id' => $reserva->getId()]);
            
            // Opción B: Mostrar un error
            //throw $this->createNotFoundException('El archivo del recibo no se encuentra en el servidor.');
        }

        $response = new BinaryFileResponse($filepath);
        
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $filename
        );

        return $response;
    }

    private function saveReceiptAsPdf(Reservas $reserva): void
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

        $output = $dompdf->output();

        $directory = $this->getParameter('reservations_directory');
        $filesystem = new Filesystem();
        
        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory, 0777);
        }

        $filename = 'comprobante_reserva_' . $reserva->getReceiptNumber() . '.pdf';
        $filepath = $directory . '/' . $filename;

        file_put_contents($filepath, $output);
    }
}