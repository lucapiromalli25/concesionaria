<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Usuarios con lo que hizo cada uno, para saber quien opera de verdad
     * antes de tocarle los permisos.
     */
    public function findAllWithActivity(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->addSelect('(SELECT COUNT(v.id) FROM App\Entity\Ventas v WHERE v.vendedor = u) AS ventas')
            ->addSelect('(SELECT COUNT(r.id) FROM App\Entity\Reservas r WHERE r.vendedor = u) AS reservas')
            ->addSelect('(SELECT COUNT(ve.id) FROM App\Entity\Vehiculos ve WHERE ve.created_by = u) AS vehiculos')
            ->leftJoin('u.usuarioRoles', 'ur')->addSelect('ur')
            ->leftJoin('ur.rol', 'rol')->addSelect('rol')
            ->orderBy('u.complete_name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * DNIs cargados mas de una vez. El login usa el DNI como identificador y la
     * columna no tiene indice unico: si se repite, la autenticacion falla.
     *
     * @return string[]
     */
    public function duplicatedDnis(): array
    {
        $filas = $this->createQueryBuilder('u')
            ->select('u.dni')
            ->groupBy('u.dni')
            ->having('COUNT(u.id) > 1')
            ->getQuery()
            ->getResult();

        return array_column($filas, 'dni');
    }
}
