<?php

namespace App\Controller;

use App\Entity\Cuotas;
use App\Entity\Ventas;
use App\Form\ModificarPlanType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/plan-de-pagos')]
#[IsGranted("is_granted('ROLE_MANAGER') or is_granted('ROLE_ADMIN') or is_granted('ROLE_PRICE')")] // Solo los gerentes pueden modificar planes
class PlanDePagosController extends AbstractController
{
    #[Route('/{id}/modificar', name: 'app_plan_de_pagos_modificar', methods: ['GET', 'POST'])]
    public function modificar(Request $request, Ventas $venta, EntityManagerInterface $entityManager): Response
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
                $newNumberOfInstallments = $form->get('numberOfInstallments')->getData();
                $venta->setNumberOfInstallments($newNumberOfInstallments);

                // 3. Generar las nuevas cuotas (misma lógica que en VentaController)
                $montoCuota = $venta->getFinalSalePrice() / $newNumberOfInstallments;
                $fechaVenta = $venta->getSaleDate();

                for ($i = 1; $i <= $newNumberOfInstallments; $i++) {
                    $cuota = new Cuotas();
                    $cuota->setVenta($venta);
                    $cuota->setInstallmentNumber($i);
                    $cuota->setAmount($montoCuota);
                    $cuota->setStatus('Pendiente');
                    $fechaVencimiento = (clone $fechaVenta)->modify("first day of +{$i} month");
                    $cuota->setDueDate($fechaVencimiento);
                    $entityManager->persist($cuota);
                }
                
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