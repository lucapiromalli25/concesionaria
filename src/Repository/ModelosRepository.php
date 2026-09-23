<?php

namespace App\Repository;

use App\Entity\Modelos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Modelos>
 */
class ModelosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Modelos::class);
    }

    private function aplicarFiltros(QueryBuilder $qb, ?string $q, ?int $marcaId): QueryBuilder
    {
        if ($q) {
            $qb->andWhere('mo.name LIKE :q OR ma.name LIKE :q')->setParameter('q', '%' . $q . '%');
        }
        if ($marcaId) {
            $qb->andWhere('ma.id = :marca')->setParameter('marca', $marcaId);
        }

        return $qb;
    }

    public function searchWithUsage(?string $q, ?int $marcaId, int $page = 1, int $perPage = 25): array
    {
        $qb = $this->createQueryBuilder('mo')
            ->select('mo', 'ma')
            ->join('mo.marca', 'ma')
            ->addSelect('(SELECT COUNT(ve.id) FROM App\Entity\Versiones ve WHERE ve.modelo = mo) AS versiones')
            ->addSelect('(SELECT COUNT(v.id) FROM App\Entity\Vehiculos v JOIN v.version ver WHERE ver.modelo = mo) AS vehiculos');

        return $this->aplicarFiltros($qb, $q, $marcaId)
            ->orderBy('ma.name', 'ASC')->addOrderBy('mo.name', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countSearch(?string $q, ?int $marcaId): int
    {
        $qb = $this->createQueryBuilder('mo')
            ->select('count(mo.id)')
            ->join('mo.marca', 'ma');

        return (int) $this->aplicarFiltros($qb, $q, $marcaId)->getQuery()->getSingleScalarResult();
    }

    /**
     * Modelos con el mismo nombre repetidos dentro de la misma marca.
     *
     * @return array<string, int> "marcaId|nombre en minuscula" => cantidad
     */
    public function duplicatedNames(): array
    {
        $filas = $this->createQueryBuilder('mo')
            ->select('ma.id AS marca, LOWER(TRIM(mo.name)) AS nombre, COUNT(mo.id) AS total')
            ->join('mo.marca', 'ma')
            ->groupBy('marca, nombre')
            ->having('COUNT(mo.id) > 1')
            ->getQuery()
            ->getResult();

        $duplicados = [];
        foreach ($filas as $fila) {
            $duplicados[$fila['marca'] . '|' . $fila['nombre']] = (int) $fila['total'];
        }

        return $duplicados;
    }
}
