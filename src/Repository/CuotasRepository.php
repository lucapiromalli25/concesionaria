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

    public function sumPaidInstallmentsByCurrency(): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('v.saleCurrency, SUM(c.amount) as total')
            ->join('c.venta', 'v')
            ->where('c.status = :status')
            ->andWhere("v.payment_method = 'Financiado'") // <-- LÍNEA CLAVE AÑADIDA
            ->setParameter('status', 'Pagada')
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

    public function sumPendingInstallmentsByCurrency(): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('v.saleCurrency, SUM(c.amount) as total')
            ->join('c.venta', 'v') // Unimos Cuotas con Ventas para saber la moneda
            ->where('c.status = :status')
            ->setParameter('status', 'Pendiente')
            ->groupBy('v.saleCurrency')
            ->getQuery()
            ->getResult();

        // Inicializamos los totales en 0 para asegurar que siempre existan
        $totals = ['ARS' => 0, 'USD' => 0];
        foreach ($results as $result) {
            if (isset($totals[$result['saleCurrency']])) {
                $totals[$result['saleCurrency']] = $result['total'];
            }
        }
        return $totals;
    }
}
