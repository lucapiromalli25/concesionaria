<?php

namespace App\Repository;

use App\Entity\Cuotas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cuotas>
 */
class CuotasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cuotas::class);
    }

    //    /**
    //     * @return Cuotas[] Returns an array of Cuotas objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cuotas
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Devuelve la suma total de lo realmente pagado en las cuotas, 
     * agrupado por la moneda en que se realizó cada pago.
     */
    public function sumPaidInstallmentsByCurrency(): array
    {
        $results = $this->createQueryBuilder('c')
            // Seleccionamos la moneda del pago (paidCurrency) y sumamos el monto pagado (paidAmount)
            ->select('c.paidCurrency, SUM(c.paidAmount) as total')
            ->where('c.status = :status')
            ->setParameter('status', 'Pagada')
            ->groupBy('c.paidCurrency')
            ->getQuery()
            ->getResult();

        $totals = ['ARS' => 0, 'USD' => 0];
        foreach ($results as $result) {
            // Verificamos que la moneda no sea null antes de asignarla
            if ($result['paidCurrency'] && isset($totals[$result['paidCurrency']])) {
                $totals[$result['paidCurrency']] = $result['total'];
            }
        }
        return $totals;
    }

    public function sumPendingInstallmentsByCurrency(): array
    {
        $results = $this->createQueryBuilder('c')
            // Seleccionamos la moneda original de la venta
            ->select('v.saleCurrency, SUM(c.amount) as total') 
            ->join('c.venta', 'v')
            ->where('c.status = :status')
            ->setParameter('status', 'Pendiente')
            ->groupBy('v.saleCurrency')
            ->getQuery()
            ->getResult();

        $totals = ['ARS' => 0, 'USD' => 0];
        foreach ($results as $result) {
            if (isset($totals[$result['saleCurrency']])) {
                $totals[$result['saleCurrency']] = $result['total'];
            }
        }
        return $totals;
    }
}
