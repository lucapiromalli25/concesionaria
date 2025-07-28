<?php

namespace App\Entity;

use App\Repository\VentasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VentasRepository::class)]
class Ventas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Vehiculos $vehiculo = null;

    #[ORM\ManyToOne]
    private ?Clientes $cliente = null;

    #[ORM\ManyToOne]
    private ?User $vendedor = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $sale_date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $final_sale_price = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $payment_method = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\ManyToOne]
    private ?User $created_by = null;

    #[ORM\ManyToOne]
    private ?User $updated_by = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehiculo(): ?Vehiculos
    {
        return $this->vehiculo;
    }

    public function setVehiculo(?Vehiculos $vehiculo): static
    {
        $this->vehiculo = $vehiculo;

        return $this;
    }

    public function getCliente(): ?Clientes
    {
        return $this->cliente;
    }

    public function setCliente(?Clientes $cliente): static
    {
        $this->cliente = $cliente;

        return $this;
    }

    public function getVendedor(): ?User
    {
        return $this->vendedor;
    }

    public function setVendedor(?User $vendedor): static
    {
        $this->vendedor = $vendedor;

        return $this;
    }

    public function getSaleDate(): ?\DateTime
    {
        return $this->sale_date;
    }

    public function setSaleDate(?\DateTime $sale_date): static
    {
        $this->sale_date = $sale_date;

        return $this;
    }

    public function getFinalSalePrice(): ?string
    {
        return $this->final_sale_price;
    }

    public function setFinalSalePrice(?string $final_sale_price): static
    {
        $this->final_sale_price = $final_sale_price;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(?string $payment_method): static
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): static
    {
        $this->created_by = $created_by;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?User $updated_by): static
    {
        $this->updated_by = $updated_by;

        return $this;
    }
}
