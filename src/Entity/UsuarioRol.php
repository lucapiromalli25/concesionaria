<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pivote usuario <-> rol. Guarda quien asigno el rol y cuando.
 * Misma decision que RolFuncionalidad: sin borrado logico.
 */
#[ORM\Entity]
#[ORM\Table(name: 'usuario_rol')]
class UsuarioRol implements CreatableInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'usuarioRoles')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $usuario = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Rol::class, inversedBy: 'usuarioRoles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Rol $rol = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    public function __construct(?User $usuario = null, ?Rol $rol = null)
    {
        $this->usuario = $usuario;
        $this->rol = $rol;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function getRol(): ?Rol
    {
        return $this->rol;
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
