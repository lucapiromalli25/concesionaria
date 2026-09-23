<?php

namespace App\EventListener;

use App\Entity\AuditableInterface;
use App\Entity\CreatableInterface;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Completa los campos de auditoria de cualquier entidad que implemente
 * CreatableInterface o AuditableInterface. Antes esto se hacia a mano en cada
 * controlador y se olvidaba seguido.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class AuditoriaListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entidad = $args->getObject();

        if (!$entidad instanceof CreatableInterface) {
            return;
        }

        if ($entidad->getCreatedAt() === null) {
            $entidad->setCreatedAt(new \DateTimeImmutable());
        }

        if ($entidad->getCreatedBy() === null) {
            $entidad->setCreatedBy($this->usuarioActual());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entidad = $args->getObject();

        if (!$entidad instanceof AuditableInterface) {
            return;
        }

        $entidad->setUpdatedAt(new \DateTimeImmutable());
        $entidad->setUpdatedBy($this->usuarioActual());

        // Borrado logico: la fecha la pone quien borra, el resto se sincroniza aca.
        if ($entidad->getDeletedAt() !== null && $entidad->getStatus() !== AuditableInterface::STATUS_INACTIVO) {
            $entidad->setStatus(AuditableInterface::STATUS_INACTIVO);

            if ($entidad->getDeletedBy() === null) {
                $entidad->setDeletedBy($this->usuarioActual());
            }
        }
    }

    private function usuarioActual(): ?User
    {
        $usuario = $this->security->getUser();

        return $usuario instanceof User ? $usuario : null;
    }
}
