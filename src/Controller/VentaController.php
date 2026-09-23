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
use App\Enum\VehicleStatus;
use App\Repository\VentasRepository;
use App\Entity\Cuotas;
use App\Service\PaymentPlanGenerator;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/ventas')]
#[IsGranted('ventas.ver')]
class VentaController extends AbstractController
{
    #[Route('/plan-preview', name: 'app_ventas_plan_preview', methods: ['GET'])]
    #[IsGranted('ventas.crear')]
    public function planPreview(Request $request, PaymentPlanGenerator $generator): JsonResponse
    {
        $count = (int) $request->query->get('count', 0);
        $price = (float) $request->query->get('price', 0);
        $date  = $request->query->get('date', date('Y-m-d'));

        if ($count <= 0 || $price <= 0) {
            return new JsonResponse([]);
        }

        return new JsonResponse($generator->generateData($price, $count, new \DateTime($date)));
    }

    #[Route('/', name: 'app_ventas_index', methods: ['GET'])]
    public function index(Request $request, VentasRepository $ventasRepository): Response
    {
        $filtros = [
            'q'      => trim((string) $request->query->get('q')) ?: null,
            'metodo' => $request->query->get('metodo') ?: null,
            'moneda' => $request->query->get('moneda') ?: null,
            'deuda'  => $request->query->get('deuda') ?: null,
            'desde'  => $request->query->get('desde') ?: null,
            'hasta'  => $request->query->get('hasta') ?: null,
        ];
        $sort = (string) $request->query->get('sort', 'fecha');
        $dir  = strtoupper((string) $request->query->get('dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $perPage    = 25;
        $total      = $ventasRepository->countSearch($filtros);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min((int) $request->query->get('page', 1), $totalPages));

        return $this->render('ventas/index.html.twig', [
            'ventas'      => $ventasRepository->search($filtros, $sort, $dir, $page, $perPage),
            'totales'     => $ventasRepository->sumSearchByCurrency($filtros),
            'metodos'     => ['Efectivo', 'Transferencia Bancaria', 'Financiado', 'Otro'],
            'filters'     => $filtros,
            'sort'        => $sort,
            'dir'         => $dir,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    // src/Controller/VentaController.php

    #[Route('/new/{id}', name: 'app_ventas_new', methods: ['GET', 'POST'])]
    #[IsGranted('ventas.crear')]
    public function new(Request $request, Vehiculos $vehiculo, EntityManagerInterface $entityManager): Response
    {
        // Verificación para no vender un auto que no está en stock o ya vendido
        if (!in_array($vehiculo->getState(), [VehicleStatus::EnStock->value, VehicleStatus::Reservado->value])) {
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
            $vehiculo->setState(VehicleStatus::Vendido->value);

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
    #[IsGranted('ventas.ver_recibo')]
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

    #[Route('/{id}/delete', name: 'app_ventas_delete', methods: ['POST'])]
    #[IsGranted('ventas.eliminar')]
    public function delete(Request $request, Ventas $venta, EntityManagerInterface $entityManager): JsonResponse
    {
        // Regla de negocio: No se puede eliminar una venta que ya tiene pagos registrados.
        if ($venta->hasPayments()) {
            return new JsonResponse([
                'status' => 'error', 
                'message' => 'No se puede eliminar una venta que ya tiene pagos registrados.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verificamos el token de seguridad
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete'.$venta->getId(), $submittedToken)) {
            
            // 1. Devolver el vehículo al stock
            $vehiculo = $venta->getVehiculo();
            if ($vehiculo) {
                $vehiculo->setState(VehicleStatus::EnStock->value);
            }

            // 2. Eliminar la venta (y sus cuotas en cascada)
            $entityManager->remove($venta);
            $entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'La venta ha sido eliminada y el vehículo ha vuelto al stock.']);
        }

        return new JsonResponse(['status' => 'error', 'message' => 'Token de seguridad inválido.'], Response::HTTP_BAD_REQUEST);
    }
}