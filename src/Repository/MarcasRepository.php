<?php

namespace App\Repository;

use App\Entity\Marcas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Marcas>
 */
class MarcasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Marcas::class);
    }

    /**
     * Marcas con cuantos modelos cuelgan de cada una y cuantos vehiculos hay
     * cargados, para poder ver que parte del catalogo se usa de verdad.
     */
    public function searchWithUsage(?string $q): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->addSelect('(SELECT COUNT(mo.id) FROM App\Entity\Modelos mo WHERE mo.marca = m) AS modelos')
            ->addSelect('(SELECT COUNT(v.id) FROM App\Entity\Vehiculos v JOIN v.version ver JOIN ver.modelo mo2 WHERE mo2.marca = m) AS vehiculos')
            ->orderBy('m.name', 'ASC');

        if ($q) {
            $qb->andWhere('m.name LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
