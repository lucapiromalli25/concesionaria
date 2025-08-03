<?php

namespace App\Entity;

use App\Repository\ImagenesVehiculosRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ImagenesVehiculosRepository::class)]
#[Vich\Uploadable] // Le dice a Vich que esta entidad maneja subidas de archivos
class ImagenesVehiculos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // --- CAMPO VIRTUAL PARA EL ARCHIVO ---
    // No se guarda en la BD. Es solo para el formulario.
    #[Vich\UploadableField(mapping: 'vehicle_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    // --- CAMPO REAL EN LA BASE DE DATOS ---
    // Aquí es donde se guarda el nombre del archivo (ej: 65f5e8b7a9f1a-mi-auto.jpg)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;
    
    #[ORM\ManyToOne(inversedBy: 'imagenesVehiculos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehiculos $vehiculo = null;
    
    // Este campo es requerido por Vich para detectar cambios
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    // (Opcional) Puedes mantener otros campos si los necesitas
    #[ORM\Column(nullable: true)]
    private ?int $orden = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isMain = null;

    // --- GETTERS Y SETTERS ---

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Si se establece un archivo, también se actualiza 'updatedAt' para que
     * el bundle sepa que la entidad ha cambiado y necesita procesar el archivo.
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): static
    {
        $this->orden = $orden;
        return $this;
    }

    public function isIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(?bool $isMain): static
    {
        $this->isMain = $isMain;
        return $this;
    }
}