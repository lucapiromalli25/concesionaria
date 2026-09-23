<?php

namespace App\Repository;

use App\Entity\Rol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rol>
 */
class RolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rol::class);
    }

    public function findOneByCodigo(string $codigo): ?Rol
    {
        return $this->findOneBy(['codigo' => $codigo]);
    }

    /**
     * Roles vigentes con sus funcionalidades y la cantidad de usuarios que los tienen.
     *
     * @return array<int, array{0: Rol, usuarios: int}>
     */
    public function findAllConUso(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', '(SELECT COUNT(ur.rol) FROM App\Entity\UsuarioRol ur WHERE ur.rol = r) AS usuarios')
            ->leftJoin('r.rolFuncionalidades', 'rf')->addSelect('rf')
            ->leftJoin('rf.funcionalidad', 'f')->addSelect('f')
            ->andWhere('r.deletedAt IS NULL')
            ->orderBy('r.esSistema', 'DESC')
            ->addOrderBy('r.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
