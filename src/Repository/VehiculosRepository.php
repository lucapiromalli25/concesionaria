<?php

namespace App\Repository;

use App\Entity\Vehiculos;
use App\Enum\VehicleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehiculos>
 */
class VehiculosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehiculos::class);
    }

    // --- MÉTODOS AÑADIDOS ---

    /**
     * Cuenta los vehículos que están "En Stock".
     */
    public function countInStock(): int
    {
        return $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->where('v.state = :state')
            ->setParameter('state', VehicleStatus::EnStock->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Cuenta los vehículos que están "Vendidos".
     */
    public function countVendidos(): int
    {
        return $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->where('v.state = :state')
            ->setParameter('state', VehicleStatus::Vendido->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Valor del stock separado por moneda, mas cuantos vehiculos quedan afuera
     * del calculo por no tener precio cargado.
     *
     * @return array{USD: float, ARS: float, sinPrecio: int, total: int}
     */
    public function inventoryValue(): array
    {
        $row = $this->createQueryBuilder('v')
            ->select(
                'SUM(CASE WHEN v.suggestedRetailPriceUsd > 0 THEN v.suggestedRetailPriceUsd ELSE 0 END) as usd',
                'SUM(CASE WHEN v.suggestedRetailPriceUsd > 0 THEN 0 ELSE COALESCE(v.suggested_retail_price, 0) END) as ars',
                'SUM(CASE WHEN COALESCE(v.suggestedRetailPriceUsd, 0) = 0 AND COALESCE(v.suggested_retail_price, 0) = 0 THEN 1 ELSE 0 END) as sinPrecio',
                'COUNT(v.id) as total'
            )
            ->where('v.state = :state')
            ->setParameter('state', VehicleStatus::EnStock->value)
            ->getQuery()
            ->getSingleResult();

        return [
            'USD'       => (float) $row['usd'],
            'ARS'       => (float) $row['ars'],
            'sinPrecio' => (int) $row['sinPrecio'],
            'total'     => (int) $row['total'],
        ];
    }

    /**
     * Devuelve los últimos vehículos ingresados al sistema.
     */
    public function findLatestArrivals(int $limit = 5): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Cuenta cuántos vehículos hay por cada marca.
     */
    public function countVehiclesByBrand(): array
    {
        return $this->createQueryBuilder('v')
            ->select('m.name, COUNT(v.id) as vehicleCount')
            ->join('v.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'm')
            ->groupBy('m.name')
            ->orderBy('vehicleCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPaginatedWithRelations(int $page, int $perPage = 25): array
    {
        return $this->createQueryBuilder('v')
            ->addSelect('ver', 'mod', 'mar')
            ->join('v.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'mar')
            ->orderBy('v.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private const SORTABLE = [
        'id'     => 'v.id',
        'anio'   => 'v.anio',
        'precio' => 'v.suggested_retail_price',
        'marca'  => 'mar.name',
        'estado' => 'v.state',
        'ingreso'=> 'v.entry_date',
    ];

    /**
     * @param array{q?: string|null, estado?: string|null, marca?: int|null, anioMin?: int|null, anioMax?: int|null} $filters
     */
    private function applyFilters(\Doctrine\ORM\QueryBuilder $qb, array $filters): \Doctrine\ORM\QueryBuilder
    {
        if (!empty($filters['q'])) {
            $qb->andWhere('v.plateNumber LIKE :q OR v.chassis_number LIKE :q OR v.engine_number LIKE :q
                           OR v.color LIKE :q OR mar.name LIKE :q OR mod.name LIKE :q OR ver.name LIKE :q')
               ->setParameter('q', '%' . $filters['q'] . '%');
        }
        if (!empty($filters['estado'])) {
            $qb->andWhere('v.state = :estado')->setParameter('estado', $filters['estado']);
        }
        if (!empty($filters['marca'])) {
            $qb->andWhere('mar.id = :marca')->setParameter('marca', $filters['marca']);
        }
        if (!empty($filters['anioMin'])) {
            $qb->andWhere('v.anio >= :anioMin')->setParameter('anioMin', $filters['anioMin']);
        }
        if (!empty($filters['anioMax'])) {
            $qb->andWhere('v.anio <= :anioMax')->setParameter('anioMax', $filters['anioMax']);
        }

        return $qb;
    }

    public function search(array $filters, string $sort = 'id', string $dir = 'DESC', int $page = 1, int $perPage = 25): array
    {
        $qb = $this->createQueryBuilder('v')
            ->addSelect('ver', 'mod', 'mar')
            ->join('v.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'mar');

        return $this->applyFilters($qb, $filters)
            ->orderBy(self::SORTABLE[$sort] ?? 'v.id', $dir === 'ASC' ? 'ASC' : 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countSearch(array $filters): int
    {
        $qb = $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->join('v.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'mar');

        return (int) $this->applyFilters($qb, $filters)->getQuery()->getSingleScalarResult();
    }

    /**
     * Totales por estado para las tarjetas de resumen del inventario.
     *
     * @return array<string, int>
     */
    public function countByState(): array
    {
        $rows = $this->createQueryBuilder('v')
            ->select('v.state, COUNT(v.id) as total')
            ->groupBy('v.state')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['state']] = (int) $row['total'];
        }

        return $counts;
    }
}