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
use Vich\UploaderBundle\Storage\StorageInterface; // <-- AÑADE ESTE IMPORT

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
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                // Generamos la URL del comprobante usando el servicio de Vich
                $receiptUrl = $cuota->getReceiptName() ? $storage->resolveUri($cuota, 'receiptFile') : null;

                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Pago registrado correctamente.',
                    'cuota' => [
                        'id' => $cuota->getId(),
                        'status' => $cuota->getStatus(),
                        'paymentDate' => $cuota->getPaymentDate()->format('d/m/Y H:i'),
                        'receiptUrl' => $receiptUrl
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
}