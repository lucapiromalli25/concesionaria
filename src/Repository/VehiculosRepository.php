<?php

namespace App\Repository;

use App\Entity\Vehiculos;
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
            ->setParameter('state', 'En Stock')
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
            ->setParameter('state', 'Vendido')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Suma el valor de venta de todos los vehículos "En Stock".
     */
    public function sumInventoryValue(): float
    {
        return $this->createQueryBuilder('v')
            ->select('SUM(v.suggested_retail_price)')
            ->where('v.state = :state')
            ->setParameter('state', 'En Stock')
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0;
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
}