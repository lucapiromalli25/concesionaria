<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pivote rol <-> funcionalidad. Guarda quien otorgo el permiso y cuando.
 *
 * No lleva borrado logico a proposito: quitar y volver a dar el mismo permiso
 * chocaria contra la clave primaria compuesta. El historial de cambios, si algun
 * dia hace falta, va en una tabla de auditoria aparte.
 */
#[ORM\Entity]
#[ORM\Table(name: 'rol_funcionalidad')]
class RolFuncionalidad implements CreatableInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Rol::class, inversedBy: 'rolFuncionalidades')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Rol $rol = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Funcionalidad::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Funcionalidad $funcionalidad = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    public function __construct(?Rol $rol = null, ?Funcionalidad $funcionalidad = null)
    {
        $this->rol = $rol;
        $this->funcionalidad = $funcionalidad;
    }

    public function getRol(): ?Rol
    {
        return $this->rol;
    }

    public function getFuncionalidad(): ?Funcionalidad
    {
        return $this->funcionalidad;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
