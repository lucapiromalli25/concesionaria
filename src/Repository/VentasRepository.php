<?php

namespace App\Repository;

use App\Entity\Ventas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ventas>
 */
class VentasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ventas::class);
    }

    // --- MÉTODO AÑADIDO ---

    /**
     * Cuenta las ventas realizadas en el mes y año actual.
     */
    public function countSalesThisMonth(): int
    {
        $startOfMonth = new \DateTimeImmutable('first day of this month midnight');
        $endOfMonth = new \DateTimeImmutable('last day of this month 23:59:59');

        return $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->where('v.sale_date BETWEEN :start AND :end')
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSalesTrend(int $days = 15): array
    {
        $startDate = new \DateTimeImmutable("-{$days} days midnight");
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DATE(v.sale_date) as sale_day, COUNT(v.id) as count
            FROM ventas v
            WHERE v.sale_date >= :start_date
            GROUP BY sale_day
            ORDER BY sale_day ASC
        ';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['start_date' => $startDate->format('Y-m-d H:i:s')]);

        return $result->fetchAllAssociative();
    }
}