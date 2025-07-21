<?php

namespace App\Entity;

use App\Repository\VehiculosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiculosRepository::class)]
class Vehiculos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Versiones $version = null;

    #[ORM\Column]
    private ?int $anio = null;

    #[ORM\Column(length: 100)]
    private ?string $chassis_number = null;

    #[ORM\Column(length: 100)]
    private ?string $engine_number = null;

    #[ORM\Column(length: 50)]
    private ?string $color = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $kilometers = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $entry_date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $purchase_price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $suggested_retail_price = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $internal_observations = null;

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

    public function getVersionId(): ?Versiones
    {
        return $this->version;
    }

    public function setVersionId(?Versiones $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getAnio(): ?int
    {
        return $this->anio;
    }

    public function setAnio(int $anio): static
    {
        $this->anio = $anio;

        return $this;
    }

    public function getChassisNumber(): ?string
    {
        return $this->chassis_number;
    }

    public function setChassisNumber(string $chassis_number): static
    {
        $this->chassis_number = $chassis_number;

        return $this;
    }

    public function getEngineNumber(): ?string
    {
        return $this->engine_number;
    }

    public function setEngineNumber(string $engine_number): static
    {
        $this->engine_number = $engine_number;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getKilometers(): ?string
    {
        return $this->kilometers;
    }

    public function setKilometers(?string $kilometers): static
    {
        $this->kilometers = $kilometers;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getEntryDate(): ?\DateTime
    {
        return $this->entry_date;
    }

    public function setEntryDate(?\DateTime $entry_date): static
    {
        $this->entry_date = $entry_date;

        return $this;
    }

    public function getPurchasePrice(): ?string
    {
        return $this->purchase_price;
    }

    public function setPurchasePrice(?string $purchase_price): static
    {
        $this->purchase_price = $purchase_price;

        return $this;
    }

    public function getSuggestedRetailPrice(): ?string
    {
        return $this->suggested_retail_price;
    }

    public function setSuggestedRetailPrice(?string $suggested_retail_price): static
    {
        $this->suggested_retail_price = $suggested_retail_price;

        return $this;
    }

    public function getInternalObservations(): ?string
    {
        return $this->internal_observations;
    }

    public function setInternalObservations(string $internal_observations): static
    {
        $this->internal_observations = $internal_observations;

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
