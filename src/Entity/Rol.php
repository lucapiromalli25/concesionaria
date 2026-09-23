<?php

namespace App\Entity;

use App\Entity\Trait\Auditable;
use App\Repository\RolRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RolRepository::class)]
#[ORM\Table(name: 'rol')]
#[UniqueEntity(fields: ['codigo'], message: 'Ya existe un rol con ese codigo.')]
class Rol implements AuditableInterface
{
    use Auditable;

    /** Rol de sistema: pasa todos los permisos y no se puede editar ni borrar. */
    public const CODIGO_ADMINISTRADOR = 'administrador';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'El codigo es obligatorio.')]
    #[Assert\Regex(pattern: '/^[a-z][a-z0-9_]*$/', message: 'Solo minusculas, numeros y guion bajo.')]
    private ?string $codigo = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'El nombre es obligatorio.')]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    /** Los roles de sistema no se pueden borrar ni cambiarles el codigo. */
    #[ORM\Column(options: ['default' => false])]
    private bool $esSistema = false;

    /** @var Collection<int, RolFuncionalidad> */
    #[ORM\OneToMany(mappedBy: 'rol', targetEntity: RolFuncionalidad::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $rolFuncionalidades;

    /** @var Collection<int, UsuarioRol> */
    #[ORM\OneToMany(mappedBy: 'rol', targetEntity: UsuarioRol::class)]
    private Collection $usuarioRoles;

    public function __construct()
    {
        $this->rolFuncionalidades = new ArrayCollection();
        $this->usuarioRoles = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->nombre;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function isEsSistema(): bool
    {
        return $this->esSistema;
    }

    public function setEsSistema(bool $esSistema): static
    {
        $this->esSistema = $esSistema;

        return $this;
    }

    public function esAdministrador(): bool
    {
        return $this->codigo === self::CODIGO_ADMINISTRADOR;
    }

    /** @return Collection<int, RolFuncionalidad> */
    public function getRolFuncionalidades(): Collection
    {
        return $this->rolFuncionalidades;
    }

    /** @return Collection<int, UsuarioRol> */
    public function getUsuarioRoles(): Collection
    {
        return $this->usuarioRoles;
    }

    public function tieneFuncionalidad(Funcionalidad $funcionalidad): bool
    {
        foreach ($this->rolFuncionalidades as $asignacion) {
            if ($asignacion->getFuncionalidad() === $funcionalidad) {
                return true;
            }
        }

        return false;
    }

    public function addFuncionalidad(Funcionalidad $funcionalidad): static
    {
        if (!$this->tieneFuncionalidad($funcionalidad)) {
            $this->rolFuncionalidades->add(new RolFuncionalidad($this, $funcionalidad));
        }

        return $this;
    }

    public function removeFuncionalidad(Funcionalidad $funcionalidad): static
    {
        foreach ($this->rolFuncionalidades as $asignacion) {
            if ($asignacion->getFuncionalidad() === $funcionalidad) {
                $this->rolFuncionalidades->removeElement($asignacion);
            }
        }

        return $this;
    }

    /**
     * Claves de las funcionalidades activas del rol.
     *
     * @return list<string>
     */
    public function getClaves(): array
    {
        $claves = [];
        foreach ($this->rolFuncionalidades as $asignacion) {
            $funcionalidad = $asignacion->getFuncionalidad();
            if ($funcionalidad->estaActivo()) {
                $claves[] = $funcionalidad->getClave();
            }
        }

        return $claves;
    }
}
