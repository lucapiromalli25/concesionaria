<?php

namespace App\Entity;

use App\Repository\ReservasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservasRepository::class)]
class Reservas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Vehiculos::class, inversedBy: 'reserva')]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?Vehiculos $vehiculo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clientes $cliente = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $vendedor = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $reservation_date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $reservation_amount = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $expiration_date = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne]
    private ?User $created_by = null;

    #[ORM\ManyToOne]
    private ?User $updatede_by = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $reservationCurrency = 'ARS';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehiculo(): ?Vehiculos
    {
        return $this->vehiculo;
    }

    public function setVehiculo(Vehiculos $vehiculo): static
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

    public function getReservationDate(): ?\DateTime
    {
        return $this->reservation_date;
    }

    public function setReservationDate(?\DateTime $reservation_date): static
    {
        $this->reservation_date = $reservation_date;

        return $this;
    }

    public function getReservationAmount(): ?string
    {
        return $this->reservation_amount;
    }

    public function setReservationAmount(?string $reservation_amount): static
    {
        $this->reservation_amount = $reservation_amount;

        return $this;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->expiration_date;
    }

    public function setExpirationDate(?\DateTime $expiration_date): static
    {
        $this->expiration_date = $expiration_date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

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

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

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

    public function getUpdatedeBy(): ?User
    {
        return $this->updatede_by;
    }

    public function setUpdatedeBy(?User $updatede_by): static
    {
        $this->updatede_by = $updatede_by;

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

    public function getReservationCurrency(): ?string
    {
        return $this->reservationCurrency;
    }

    public function setReservationCurrency(?string $reservationCurrency): static
    {
        $this->reservationCurrency = $reservationCurrency;
        return $this;
    }
}
