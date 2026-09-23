<?php

namespace App\Repository;

use App\Entity\Versiones;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Versiones>
 */
class VersionesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Versiones::class);
    }

    private function aplicarFiltros(QueryBuilder $qb, ?string $q, ?string $uso): QueryBuilder
    {
        if ($q) {
            $qb->andWhere('ve.name LIKE :q OR mo.name LIKE :q OR ma.name LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }
        if ($uso === 'sin-uso') {
            $qb->andWhere('NOT EXISTS (SELECT 1 FROM App\Entity\Vehiculos v2 WHERE v2.version = ve)');
        } elseif ($uso === 'sin-nombre') {
            $qb->andWhere("ve.name IS NULL OR TRIM(ve.name) = ''");
        }

        return $qb;
    }

    public function searchWithUsage(?string $q, ?string $uso, int $page = 1, int $perPage = 25): array
    {
        $qb = $this->createQueryBuilder('ve')
            ->select('ve', 'mo', 'ma')
            ->join('ve.modelo', 'mo')
            ->join('mo.marca', 'ma')
            ->addSelect('(SELECT COUNT(v.id) FROM App\Entity\Vehiculos v WHERE v.version = ve) AS vehiculos');

        return $this->aplicarFiltros($qb, $q, $uso)
            ->orderBy('ma.name', 'ASC')->addOrderBy('mo.name', 'ASC')->addOrderBy('ve.name', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countSearch(?string $q, ?string $uso): int
    {
        $qb = $this->createQueryBuilder('ve')
            ->select('count(ve.id)')
            ->join('ve.modelo', 'mo')
            ->join('mo.marca', 'ma');

        return (int) $this->aplicarFiltros($qb, $q, $uso)->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array{total: int, sinUso: int, sinNombre: int}
     */
    public function usageSummary(): array
    {
        return [
            'total'      => $this->countSearch(null, null),
            'sinUso'     => $this->countSearch(null, 'sin-uso'),
            'sinNombre'  => $this->countSearch(null, 'sin-nombre'),
        ];
    }
}
