<?php

namespace App\Repository;

use App\Entity\Clientes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Clientes>
 */
class ClientesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clientes::class);
    }

    private const SORTABLE = [
        'apellido'  => 'c.last_name',
        'documento' => 'c.document_number',
        'alta'      => 'c.created_at',
    ];

    private function aplicarFiltros(QueryBuilder $qb, ?string $q): QueryBuilder
    {
        if ($q) {
            $qb->andWhere('c.first_name LIKE :q OR c.last_name LIKE :q OR c.document_number LIKE :q OR c.phone LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        return $qb;
    }

    /**
     * Listado con la cantidad de operaciones de cada cliente, en una sola query.
     *
     * @return array<int, array{0: Clientes, ventas: int, reservas: int}>
     */
    public function search(?string $q, string $sort = 'apellido', string $dir = 'ASC', int $page = 1, int $perPage = 25): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->addSelect('(SELECT COUNT(v.id) FROM App\Entity\Ventas v WHERE v.cliente = c) AS ventas')
            ->addSelect('(SELECT COUNT(r.id) FROM App\Entity\Reservas r WHERE r.cliente = c) AS reservas');

        return $this->aplicarFiltros($qb, $q)
            ->orderBy(self::SORTABLE[$sort] ?? 'c.last_name', $dir === 'DESC' ? 'DESC' : 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countSearch(?string $q): int
    {
        $qb = $this->createQueryBuilder('c')->select('count(c.id)');

        return (int) $this->aplicarFiltros($qb, $q)->getQuery()->getSingleScalarResult();
    }
}
