<?php

namespace App\Controller;

use App\Entity\Cuotas;
use App\Form\CuotaPaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Storage\StorageInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/cuotas')]
#[IsGranted('ROLE_USER')]
class CuotaController extends AbstractController
{
    #[Route('/{id}/register-payment', name: 'app_cuotas_register_payment', methods: ['GET', 'POST'])]
    public function registerPayment(Request $request, Cuotas $cuota, EntityManagerInterface $entityManager, StorageInterface $storage): Response
    {
        $form = $this->createForm(CuotaPaymentType::class, $cuota);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cuota->setStatus('Pagada');

            // --- LÓGICA DE PAGO MEJORADA ---
            // Si el vendedor no especificó un monto, asumimos el pago completo de la cuota.
            if ($cuota->getPaidAmount() === null || $cuota->getPaidAmount() == 0) {
                $cuota->setPaidAmount($cuota->getAmount());
            }
            // Si no especificó una moneda, asumimos la moneda original de la venta.
            if ($cuota->getPaidCurrency() === null) {
                $cuota->setPaidCurrency($cuota->getVenta()->getSaleCurrency());
            }

            $entityManager->flush(); // Guardamos los datos del pago.

            // Generamos el número de recibo único después de guardar.
            $receiptNumber = 'RC-' . str_pad($cuota->getVenta()->getId(), 4, '0', STR_PAD_LEFT) . '-' . str_pad($cuota->getId(), 5, '0', STR_PAD_LEFT);
            $cuota->setReceiptNumber($receiptNumber);
            $entityManager->flush(); // Volvemos a guardar para persistir el número de recibo.

            if ($request->isXmlHttpRequest()) {
                // Obtenemos la URL del COMPROBANTE (archivo subido por el cliente)
                $comprobanteUrl = $cuota->getReceiptName() ? $storage->resolveUri($cuota, 'receiptFile') : null;
                // Generamos la URL para el RECIBO (PDF que genera nuestro sistema)
                $reciboPdfUrl = $this->generateUrl('app_cuotas_receipt', ['id' => $cuota->getId()]);

                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Pago registrado correctamente.',
                    'cuota' => [
                        'id' => $cuota->getId(),
                        'status' => $cuota->getStatus(),
                        'paymentDate' => $cuota->getPaymentDate()->format('d/m/Y H:i'),
                        'comprobanteUrl' => $comprobanteUrl,
                        'reciboUrl' => $reciboPdfUrl,
                    ]
                ]);
            }
            
            $this->addFlash('success', 'Pago registrado correctamente.');
            return $this->redirectToRoute('app_ventas_show', ['id' => $cuota->getVenta()->getId()]);
        }

        return $this->render('ventas/_payment_form_modal.html.twig', [
            'form' => $form->createView(),
            'cuota' => $cuota
        ]);
    }

    #[Route('/{id}/receipt', name: 'app_cuotas_receipt')]
    public function receipt(Cuotas $cuota): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        // Pasamos la cuota a la misma plantilla, que ahora es más inteligente
        $html = $this->renderView('receipt/receipt_template.html.twig', ['cuota' => $cuota]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'recibo_cuota_' . $cuota->getReceiptNumber() . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);

        return new Response('', 200, ['Content-Type' => 'application/pdf']);
    }
}