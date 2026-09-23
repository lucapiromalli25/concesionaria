<?php

namespace App\Controller;

use App\Entity\Ventas;
use App\Form\ModificarPlanType;
use App\Service\PaymentPlanGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/plan-de-pagos')]
#[IsGranted('planes_pago.modificar')]
class PlanDePagosController extends AbstractController
{
    #[Route('/{id}/modificar', name: 'app_plan_de_pagos_modificar', methods: ['GET', 'POST'])]
    public function modificar(Request $request, Ventas $venta, EntityManagerInterface $entityManager, PaymentPlanGenerator $generator): Response
    {
        // Regla de negocio: No se puede modificar si ya hay pagos
        if ($venta->hasPayments()) {
            $this->addFlash('danger', 'No se puede modificar un plan con pagos ya registrados.');
            return $this->redirectToRoute('app_ventas_show', ['id' => $venta->getId()]);
        }

        $form = $this->createForm(ModificarPlanType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->beginTransaction();
            try {
                // 1. Borrar todas las cuotas existentes
                foreach ($venta->getCuotas() as $cuota) {
                    $entityManager->remove($cuota);
                }
                $entityManager->flush(); // Aplicamos el borrado

                // 2. Actualizar la venta con el nuevo número de cuotas
                $venta->setNumberOfInstallments($form->get('numberOfInstallments')->getData());

                // 3. Generar las nuevas cuotas
                $generator->persistForVenta($venta, $entityManager);
                
                $entityManager->flush();
                $entityManager->commit();
                
                $this->addFlash('success', 'El plan de pagos ha sido actualizado correctamente.');

            } catch (\Exception $e) {
                $entityManager->rollback();
                $this->addFlash('danger', 'Ocurrió un error al actualizar el plan de pagos.');
            }

            return $this->redirectToRoute('app_ventas_show', ['id' => $venta->getId()]);
        }

        return $this->render('ventas/_modificar_plan_modal.html.twig', [
            'form' => $form->createView(),
            'venta' => $venta
        ]);
    }
}