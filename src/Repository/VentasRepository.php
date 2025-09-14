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

    /**
     * Devuelve el valor total de todas las ventas históricas.
     */
    public function getTotalSalesValue(): float
    {
        return $this->createQueryBuilder('v')
            ->select('SUM(v.final_sale_price)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0;
    }

    /**
     * Devuelve el total de ventas y el monto agrupado por mes para el último año.
     */
    public function getSalesByMonth(): array
    {
        return $this->createQueryBuilder('v')
            // Usamos los nombres de propiedad exactos de tu entidad
            ->select('YEAR(v.sale_date) as sales_year, MONTH(v.sale_date) as sales_month, COUNT(v.id) as sales_count, SUM(v.final_sale_price) as total_amount')
            ->where('v.sale_date > :one_year_ago')
            ->setParameter('one_year_ago', new \DateTimeImmutable('-1 year'))
            ->groupBy('sales_year, sales_month')
            ->orderBy('sales_year, sales_month')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Devuelve las marcas más vendidas, ordenadas por cantidad.
     */
    public function getTopSellingBrands(int $limit = 5): array
    {
        return $this->createQueryBuilder('v')
            ->select('m.name, COUNT(v.id) as salesCount')
            ->join('v.vehiculo', 'veh')
            ->join('veh.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'm')
            ->groupBy('m.name')
            ->orderBy('salesCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve los vendedores con más ventas en el mes actual.
     */
    public function findTopSalespersonsThisMonth(int $limit = 3): array
    {
        $startOfMonth = new \DateTimeImmutable('first day of this month midnight');
        $endOfMonth = new \DateTimeImmutable('last day of this month 23:59:59');

        return $this->createQueryBuilder('v')
            ->select('s.complete_name, s.email, COUNT(v.id) as salesCount')
            ->join('v.vendedor', 's')
            ->where('v.sale_date BETWEEN :start AND :end')
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->groupBy('s.id')
            ->orderBy('salesCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findSalesCountAndAmountBySalesperson(): array
    {
        return $this->createQueryBuilder('v')
            ->select('u.complete_name as salespersonName, COUNT(v.id) as salesCount, SUM(v.final_sale_price) as salesAmount')
            ->join('v.vendedor', 'u') // Asume que la relación en tu entidad Ventas es 'vendedor'
            ->groupBy('salespersonName')
            ->orderBy('salesCount', 'DESC')
            ->setMaxResults(5) // Limita a los 5 principales para que el gráfico no sea muy grande
            ->getQuery()
            ->getResult();
    }

    public function getNonFinancedSalesValueByCurrency(): array
    {
        $results = $this->createQueryBuilder('v')
            ->select('v.saleCurrency, SUM(v.final_sale_price) as total')
            ->where("v.payment_method != 'Financiado'")
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