<?php

namespace App\Service;

use App\Entity\Cuotas;
use App\Entity\Ventas;
use Doctrine\ORM\EntityManagerInterface;

class PaymentPlanGenerator
{
    public function generateData(float $totalAmount, int $count, \DateTimeInterface $startDate): array
    {
        $montoCuota = round($totalAmount / $count, 2);
        $result = [];
        for ($i = 1; $i <= $count; $i++) {
            $dueDate = (clone $startDate)->modify("first day of +{$i} month");
            $result[] = [
                'installmentNumber' => $i,
                'amount'            => $montoCuota,
                'dueDate'           => $dueDate->format('Y-m-d'),
            ];
        }
        return $result;
    }

    public function persistForVenta(Ventas $venta, EntityManagerInterface $em): void
    {
        $data = $this->generateData(
            (float) $venta->getFinalSalePrice(),
            $venta->getNumberOfInstallments(),
            $venta->getSaleDate()
        );
        foreach ($data as $item) {
            $cuota = new Cuotas();
            $cuota->setVenta($venta);
            $cuota->setInstallmentNumber($item['installmentNumber']);
            $cuota->setAmount((string) $item['amount']);
            $cuota->setDueDate(new \DateTime($item['dueDate']));
            $cuota->setStatus('Pendiente');
            $em->persist($cuota);
        }
    }
}
