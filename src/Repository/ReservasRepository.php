<?php

namespace App\Repository;

use App\Entity\Reservas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservas>
 */
class ReservasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservas::class);
    }

    private function aplicarFiltros(QueryBuilder $qb, ?string $q, ?string $estado): QueryBuilder
    {
        if ($q) {
            $qb->andWhere('cli.first_name LIKE :q OR cli.last_name LIKE :q OR cli.document_number LIKE :q
                           OR veh.plateNumber LIKE :q OR mar.name LIKE :q OR mod.name LIKE :q OR r.receiptNumber LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        if ($estado === 'vencida') {
            $qb->andWhere('r.status = :activa')
               ->andWhere('r.expiration_date < :hoy')
               ->setParameter('activa', 'Activa')
               ->setParameter('hoy', new \DateTimeImmutable('today'));
        } elseif ($estado) {
            $qb->andWhere('r.status = :estado')->setParameter('estado', $estado);
        }

        return $qb;
    }

    private function base(): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->join('r.cliente', 'cli')
            ->join('r.vehiculo', 'veh')
            ->join('veh.version', 'ver')
            ->join('ver.modelo', 'mod')
            ->join('mod.marca', 'mar');
    }

    public function search(?string $q, ?string $estado, int $page = 1, int $perPage = 25): array
    {
        $qb = $this->base()->addSelect('cli', 'veh', 'ver', 'mod', 'mar');

        return $this->aplicarFiltros($qb, $q, $estado)
            ->orderBy('r.reservation_date', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countSearch(?string $q, ?string $estado): int
    {
        $qb = $this->base()->select('count(r.id)');

        return (int) $this->aplicarFiltros($qb, $q, $estado)->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array{activas: int, vencidas: int, total: int}
     */
    public function summary(): array
    {
        return [
            'total'    => $this->countSearch(null, null),
            'activas'  => $this->countSearch(null, 'Activa'),
            'vencidas' => $this->countSearch(null, 'vencida'),
        ];
    }
}
