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
use App\Entity\Cuotas;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    // src/Controller/VentaController.php

    #[Route('/new/{id}', name: 'app_ventas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Vehiculos $vehiculo, EntityManagerInterface $entityManager): Response
    {
        // Verificación para no vender un auto que no está en stock o ya vendido
        if (!in_array($vehiculo->getState(), ['En Stock', 'Reservado'])) {
            $this->addFlash('danger', 'Este vehículo no está disponible para la venta.');
            return $this->redirectToRoute('app_vehiculos_index');
        }

        $venta = new Ventas();
        $venta->setVehiculo($vehiculo);

        // Si el auto está reservado, pre-seleccionamos al cliente y el precio
        if ($reserva = $vehiculo->getReserva()) {
            $venta->setCliente($reserva->getCliente());
            $venta->setFinalSalePrice($vehiculo->getSuggestedRetailPrice());
        }

        $form = $this->createForm(VentaType::class, $venta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar el vendedor (usuario actual)
            $venta->setVendedor($this->getUser());
            
            // Cambiar el estado del vehículo a 'Vendido'
            $vehiculo->setState('Vendido');

            // Si existía una reserva, se marca como 'Completada'
            if ($reserva = $vehiculo->getReserva()) {
                $reserva->setStatus('Completada');
            }

            // Lógica de auditoría para la Venta
            $venta->setCreatedBy($this->getUser());
            $venta->setCreatedAt(new \DateTimeImmutable());
            $venta->setUpdatedBy($this->getUser());
            $venta->setUpdatedAt(new \DateTimeImmutable());
            
            $entityManager->persist($venta);

            // --- LÓGICA PARA PROCESAR CUOTAS ---
            $installmentsDataJson = $request->request->get('installments_data');
            $installmentsData = json_decode($installmentsDataJson, true);

            if ($venta->getPaymentMethod() === 'Financiado' && is_array($installmentsData)) {
                $numeroDeCuota = 1;
                foreach ($installmentsData as $data) {
                    $cuota = new Cuotas();
                    $cuota->setVenta($venta);
                    $cuota->setInstallmentNumber($numeroDeCuota);
                    $cuota->setAmount($data['amount']);
                    $cuota->setDueDate(new \DateTimeImmutable($data['dueDate']));
                    $cuota->setStatus('Pendiente');
                    
                    $entityManager->persist($cuota);
                    $numeroDeCuota++;
                }
                $venta->setNumberOfInstallments(count($installmentsData));
            }
            
            // 1. PRIMER GUARDADO: Se guarda la venta y las cuotas, y se genera el ID de la venta.
            $entityManager->flush(); 

            // 2. GENERACIÓN Y GUARDADO DEL NÚMERO DE RECIBO
            
            $venta->setReceiptNumber('RV-' . str_pad($venta->getId(), 6, '0', STR_PAD_LEFT));
            // 3. SEGUNDO GUARDADO: Persistimos el número de recibo que acabamos de generar.
            $entityManager->flush(); 
            

            $this->addFlash('success', '¡Venta registrada con éxito!');
            return $this->redirectToRoute('app_ventas_success', ['id' => $venta->getId()]);
        }

        return $this->render('ventas/new.html.twig', [
            'vehiculo' => $vehiculo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_ventas_show', methods: ['GET'])]
    public function show(Ventas $venta): Response
    {
        return $this->render('ventas/show.html.twig', [
            'venta' => $venta,
        ]);
    }

    #[Route('/{id}/success', name: 'app_ventas_success')]
    public function saleSuccess(Ventas $venta): Response
    {
        return $this->render('ventas/success.html.twig', [
            'venta' => $venta
        ]);
    }

    #[Route('/{id}/receipt', name: 'app_ventas_receipt')]
    public function receipt(Ventas $venta): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('receipt/receipt_template.html.twig', ['venta' => $venta]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'recibo_venta_' . $venta->getReceiptNumber() . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]); // true para forzar la descarga

        return new Response('', 200, ['Content-Type' => 'application/pdf']);
    }
}