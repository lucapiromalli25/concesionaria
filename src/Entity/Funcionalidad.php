<?php

namespace App\Entity;

use App\Entity\Trait\Auditable;
use App\Repository\FuncionalidadRepository;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Una accion que el sistema sabe controlar: "vehiculos.editar", "ventas.anular_pago".
 *
 * El catalogo se define en config/permisos.php y se vuelca a esta tabla con
 * `app:permisos:sincronizar`. Desde el backoffice se asignan a los roles, pero no
 * se crean a mano: una clave que el codigo no chequea no hace nada.
 */
#[ORM\Entity(repositoryClass: FuncionalidadRepository::class)]
#[ORM\Table(name: 'funcionalidad')]
#[UniqueEntity(fields: ['clave'], message: 'Ya existe una funcionalidad con esa clave.')]
class Funcionalidad implements AuditableInterface
{
    use Auditable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-z_]+\.[a-z_]+$/', message: 'El formato es modulo.accion, en minusculas.')]
    private ?string $clave = null;

    /** Agrupador para la matriz del backoffice: "Vehiculos", "Ventas". */
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $modulo = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $orden = 0;

    public function __toString(): string
    {
        return (string) $this->nombre;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClave(): ?string
    {
        return $this->clave;
    }

    public function setClave(string $clave): static
    {
        $this->clave = $clave;

        return $this;
    }

    public function getModulo(): ?string
    {
        return $this->modulo;
    }

    public function setModulo(string $modulo): static
    {
        $this->modulo = $modulo;

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

    public function getOrden(): int
    {
        return $this->orden;
    }

    public function setOrden(int $orden): static
    {
        $this->orden = $orden;

        return $this;
    }
}
