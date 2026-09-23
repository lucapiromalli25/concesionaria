<?php

namespace App\Repository;

use App\Entity\Proveedores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Proveedores>
 */
class ProveedoresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proveedores::class);
    }

    private function aplicarFiltros(QueryBuilder $qb, ?string $q): QueryBuilder
    {
        if ($q) {
            $qb->andWhere('p.name LIKE :q OR p.documentNumber LIKE :q OR p.contactPerson LIKE :q OR p.phone LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        return $qb;
    }

    /**
     * Listado con la cantidad de vehiculos comprados a cada proveedor.
     */
    public function search(?string $q, int $page = 1, int $perPage = 25): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->addSelect('(SELECT COUNT(v.id) FROM App\Entity\Vehiculos v WHERE v.supplier = p) AS vehiculos');

        return $this->aplicarFiltros($qb, $q)
            ->orderBy('p.name', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countSearch(?string $q): int
    {
        $qb = $this->createQueryBuilder('p')->select('count(p.id)');

        return (int) $this->aplicarFiltros($qb, $q)->getQuery()->getSingleScalarResult();
    }

    /**
     * Nombres cargados mas de una vez: el alta rapida crea un proveedor por
     * operacion y la lista quedo llena de repetidos.
     *
     * @return array<string, int> nombre normalizado => cantidad
     */
    public function duplicatedNames(): array
    {
        $filas = $this->createQueryBuilder('p')
            ->select('LOWER(TRIM(p.name)) AS nombre, COUNT(p.id) AS total')
            ->groupBy('nombre')
            ->having('COUNT(p.id) > 1')
            ->getQuery()
            ->getResult();

        return array_column($filas, 'total', 'nombre');
    }
}
