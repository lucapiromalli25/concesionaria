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

    private const SORTABLE = [
        'fecha'   => 'v.sale_date',
        'precio'  => 'v.final_sale_price',
        'cliente' => 'cli.last_name',
        'recibo'  => 'v.receiptNumber',
    ];

    /**
     * @param array{q?: string|null, metodo?: string|null, moneda?: string|null, deuda?: string|null, desde?: string|null, hasta?: string|null} $filtros
     */
    private function aplicarFiltros(\Doctrine\ORM\QueryBuilder $qb, array $filtros): \Doctrine\ORM\QueryBuilder
    {
        if (!empty($filtros['q'])) {
            $qb->andWhere('cli.first_name LIKE :q OR cli.last_name LIKE :q OR cli.document_number LIKE :q
                           OR veh.plateNumber LIKE :q OR mar.name LIKE :q OR mod.name LIKE :q OR v.receiptNumber LIKE :q')
               ->setParameter('q', '%' . $filtros['q'] . '%');
        }
        if (!empty($filtros['metodo'])) {
            $qb->andWhere('v.payment_method = :metodo')->setParameter('metodo', $filtros['metodo']);
        }
        if (!empty($filtros['moneda'])) {
            $qb->andWhere('v.saleCurrency = :moneda')->setParameter('moneda', $filtros['moneda']);
        }
        if (!empty($filtros['desde'])) {
            $qb->andWhere('v.sale_date >= :desde')->setParameter('desde', new \DateTimeImmutable($filtros['desde']));
        }
        if (!empty($filtros['hasta'])) {
            $qb->andWhere('v.sale_date <= :hasta')->setParameter('hasta', new \DateTimeImmutable($filtros['hasta'] . ' 23:59:59'));
        }
        if (!empty($filtros['deuda'])) {
            $subquery = 'SELECT 1 FROM App\Entity\Cuotas c2 WHERE c2.venta = v AND c2.status = :pendiente';
            if ($filtros['deuda'] === 'con') {
                $qb->andWhere("EXISTS ($subquery)")->setParameter('pendiente', 'Pendiente');
            } elseif ($filtros['deuda'] === 'vencida') {
                $qb->andWhere("EXISTS ($subquery AND c2.dueDate < :hoy)")
                   ->setParameter('pendiente', 'Pendiente')
                   ->setParameter('hoy', new \DateTimeImmutable('today'));
            } elseif ($filtros['deuda'] === 'sin') {
                $qb->andWhere("NOT EXISTS ($subquery)")->setParameter('pendiente', 'Pendiente');
            }
        }

        return $qb;
    }

    public function search(array $filtros, string $sort = 'fecha', string $dir = 'DESC', int $page = 1, int $perPage = 25): array
    {
        $qb = $this->createQueryBuilder('v')
            ->addSelect('cli', 'veh', 'ver', 'mod', 'mar', 'vend')
            ->join('v.cliente', 'cli')
            ->join('v.vehiculo', 'veh')
            ->join('veh.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'mar')
            ->leftJoin('v.vendedor', 'vend');

        return $this->aplicarFiltros($qb, $filtros)
            ->orderBy(self::SORTABLE[$sort] ?? 'v.sale_date', $dir === 'ASC' ? 'ASC' : 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countSearch(array $filtros): int
    {
        $qb = $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->join('v.cliente', 'cli')
            ->join('v.vehiculo', 'veh')
            ->join('veh.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'mar');

        return (int) $this->aplicarFiltros($qb, $filtros)->getQuery()->getSingleScalarResult();
    }

    /**
     * Totales por moneda del resultado filtrado, para mostrarlos arriba del listado.
     *
     * @return array<string, float>
     */
    public function sumSearchByCurrency(array $filtros): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('v.saleCurrency as moneda, SUM(v.final_sale_price) as total')
            ->join('v.cliente', 'cli')
            ->join('v.vehiculo', 'veh')
            ->join('veh.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'mar')
            ->groupBy('v.saleCurrency');

        $totales = ['ARS' => 0.0, 'USD' => 0.0];
        foreach ($this->aplicarFiltros($qb, $filtros)->getQuery()->getResult() as $fila) {
            $totales[$fila['moneda'] ?: 'ARS'] = (float) $fila['total'];
        }

        return $totales;
    }

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
     * Valor total vendido, separado por moneda: sumar pesos con dolares daria
     * un numero sin sentido.
     *
     * @return array<string, float>
     */
    public function getTotalSalesValueByCurrency(): array
    {
        $rows = $this->createQueryBuilder('v')
            ->select('v.saleCurrency as moneda, SUM(v.final_sale_price) as total')
            ->groupBy('v.saleCurrency')
            ->getQuery()
            ->getResult();

        $totales = ['ARS' => 0.0, 'USD' => 0.0];
        foreach ($rows as $row) {
            $totales[$row['moneda'] ?: 'ARS'] = (float) $row['total'];
        }

        return $totales;
    }

    /**
     * Cantidad y monto por mes y por moneda, para los ultimos 12 meses.
     */
    public function getSalesByMonth(): array
    {
        return $this->createQueryBuilder('v')
            ->select('YEAR(v.sale_date) as sales_year, MONTH(v.sale_date) as sales_month, v.saleCurrency as moneda,
                      COUNT(v.id) as sales_count, SUM(v.final_sale_price) as total_amount')
            ->where('v.sale_date > :one_year_ago')
            ->setParameter('one_year_ago', new \DateTimeImmutable('-1 year'))
            ->groupBy('sales_year, sales_month, moneda')
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

    /**
     * Ranking de vendedores por cantidad de operaciones (los montos van
     * separados por moneda porque no son sumables entre si).
     */
    public function findSalesCountAndAmountBySalesperson(int $limit = 5): array
    {
        $rows = $this->createQueryBuilder('v')
            ->select('u.complete_name as vendedor, v.saleCurrency as moneda, COUNT(v.id) as cantidad, SUM(v.final_sale_price) as monto')
            ->join('v.vendedor', 'u')
            ->groupBy('vendedor, moneda')
            ->getQuery()
            ->getResult();

        $porVendedor = [];
        foreach ($rows as $row) {
            $nombre = $row['vendedor'];
            $porVendedor[$nombre] ??= ['vendedor' => $nombre, 'cantidad' => 0, 'montos' => ['ARS' => 0.0, 'USD' => 0.0]];
            $porVendedor[$nombre]['cantidad'] += (int) $row['cantidad'];
            $porVendedor[$nombre]['montos'][$row['moneda'] ?: 'ARS'] += (float) $row['monto'];
        }

        usort($porVendedor, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

        return array_slice($porVendedor, 0, $limit);
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