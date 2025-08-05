<?php

namespace App\Entity;

use App\Repository\VehiculosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reservas;
use App\Entity\Proveedores;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: VehiculosRepository::class)]
#[Vich\Uploadable]
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
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

    /**
     * @var Collection<int, ImagenesVehiculos>
     */
    #[ORM\OneToMany(mappedBy: 'vehiculo', targetEntity: ImagenesVehiculos::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $imagenesVehiculos;

    #[ORM\OneToOne(mappedBy: 'vehiculo', cascade: ['persist', 'remove'])]
    private ?Ventas $venta = null;

    #[ORM\OneToOne(mappedBy: 'vehiculo', cascade: ['persist', 'remove'])]
    private ?Reservas $reserva = null;

    public function __construct()
    {
        $this->imagenesVehiculos = new ArrayCollection();
    }

    #[ORM\Column(length: 20, nullable: true, unique: true)]
    private ?string $plateNumber = null;

    #[ORM\ManyToOne(inversedBy: 'vehiculosComprados')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Proveedores $supplier = null;

    #[Vich\UploadableField(mapping: 'purchase_documents', fileNameProperty: 'purchaseDocumentName')]
    private ?File $purchaseDocumentFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $purchaseDocumentName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): ?Versiones
    {
        return $this->version;
    }

    public function setVersion(?Versiones $version): static
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

    /**
     * @return Collection<int, ImagenesVehiculos>
     */
    public function getImagenesVehiculos(): Collection
    {
        return $this->imagenesVehiculos;
    }

    public function addImagenesVehiculo(ImagenesVehiculos $imagenesVehiculo): static
    {
        if (!$this->imagenesVehiculos->contains($imagenesVehiculo)) {
            $this->imagenesVehiculos->add($imagenesVehiculo);
            $imagenesVehiculo->setVehiculo($this);
        }

        return $this;
    }

    public function removeImagenesVehiculo(ImagenesVehiculos $imagenesVehiculo): static
    {
        if ($this->imagenesVehiculos->removeElement($imagenesVehiculo)) {
            // set the owning side to null (unless already changed)
            if ($imagenesVehiculo->getVehiculo() === $this) {
                $imagenesVehiculo->setVehiculo(null);
            }

            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getVenta(): ?Ventas
    {
        return $this->venta;
    }

    public function setVenta(Ventas $venta): static
    {
        // set the owning side of the relation if necessary
        if ($venta->getVehiculo() !== $this) {
            $venta->setVehiculo($this);
        }

        $this->venta = $venta;

        return $this;
    }

    public function getReserva(): ?Reservas
    {
        return $this->reserva;
    }

    public function setReserva(Reservas $reserva): static
    {
        if ($reserva->getVehiculo() !== $this) {
            $reserva->setVehiculo($this);
        }
        $this->reserva = $reserva;
        return $this;
    }
    
    public function getPlateNumber(): ?string
    {
        return $this->plateNumber;
    }

    public function setPlateNumber(?string $plateNumber): static
    {
        $this->plateNumber = $plateNumber;

        return $this;
    }

    public function getSupplier(): ?Proveedores
    {
        return $this->supplier;
    }

    public function setSupplier(?Proveedores $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function setPurchaseDocumentFile(?File $purchaseDocumentFile = null): void
    {
        $this->purchaseDocumentFile = $purchaseDocumentFile;
        if (null !== $purchaseDocumentFile) {
            // Es necesario para que el bundle sepa que hubo un cambio
            $this->setUpdatedAt(new \DateTimeImmutable());
        }
    }

    public function getPurchaseDocumentFile(): ?File
    {
        return $this->purchaseDocumentFile;
    }

    public function setPurchaseDocumentName(?string $purchaseDocumentName): void
    {
        $this->purchaseDocumentName = $purchaseDocumentName;
    }

    public function getPurchaseDocumentName(): ?string
    {
        return $this->purchaseDocumentName;
    }

}
