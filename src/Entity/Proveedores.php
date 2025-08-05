<?php

namespace App\Entity;

use App\Repository\ProveedoresRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProveedoresRepository::class)]
class Proveedores
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactPerson = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $documentNumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    /**
     * @var Collection<int, Vehiculos>
     */
    #[ORM\OneToMany(mappedBy: 'supplier', targetEntity: Vehiculos::class)]
    private Collection $vehiculosComprados;

    public function __construct()
    {
        $this->vehiculosComprados = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(?string $contactPerson): static
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(?string $documentNumber): static
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Vehiculos>
     */
    public function getVehiculosComprados(): Collection
    {
        return $this->vehiculosComprados;
    }

    public function addVehiculosComprado(Vehiculos $vehiculosComprado): static
    {
        if (!$this->vehiculosComprados->contains($vehiculosComprado)) {
            $this->vehiculosComprados->add($vehiculosComprado);
            $vehiculosComprado->setSupplier($this);
        }

        return $this;
    }

    public function removeVehiculosComprado(Vehiculos $vehiculosComprado): static
    {
        if ($this->vehiculosComprados->removeElement($vehiculosComprado)) {
            // set the owning side to null (unless already changed)
            if ($vehiculosComprado->getSupplier() === $this) {
                $vehiculosComprado->setSupplier(null);
            }
        }

        return $this;
    }
}
