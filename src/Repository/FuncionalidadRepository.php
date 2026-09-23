<?php

namespace App\Repository;

use App\Entity\Funcionalidad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Funcionalidad>
 */
class FuncionalidadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Funcionalidad::class);
    }

    /** @return array<string, Funcionalidad> Indexadas por clave. */
    public function findAllIndexadas(): array
    {
        $porClave = [];
        foreach ($this->findAll() as $funcionalidad) {
            $porClave[$funcionalidad->getClave()] = $funcionalidad;
        }

        return $porClave;
    }

    /**
     * Funcionalidades activas agrupadas por modulo, para la matriz del backoffice.
     *
     * @return array<string, list<Funcionalidad>>
     */
    public function findAgrupadasPorModulo(): array
    {
        $funcionalidades = $this->createQueryBuilder('f')
            ->andWhere('f.deletedAt IS NULL')
            ->andWhere('f.status = :activo')->setParameter('activo', Funcionalidad::STATUS_ACTIVO)
            ->orderBy('f.orden', 'ASC')
            ->addOrderBy('f.clave', 'ASC')
            ->getQuery()
            ->getResult();

        $porModulo = [];
        foreach ($funcionalidades as $funcionalidad) {
            $porModulo[$funcionalidad->getModulo()][] = $funcionalidad;
        }

        return $porModulo;
    }
}
