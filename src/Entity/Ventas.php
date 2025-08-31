<?php

namespace App\Entity;

use App\Repository\VentasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File; // <-- AÑADE ESTE IMPORT
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: VentasRepository::class)]
#[Vich\Uploadable]
class Ventas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Vehiculos::class, inversedBy: 'venta')]
    #[ORM\JoinColumn(nullable: false, unique: true)]
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

    #[ORM\OneToMany(mappedBy: 'venta', targetEntity: Cuotas::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $cuotas;

    #[ORM\Column(nullable: true)]
    private ?int $numberOfInstallments = null;

    #[Vich\UploadableField(mapping: 'sale_documents', fileNameProperty: 'saleDocumentName')]
    private ?File $saleDocumentFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $saleDocumentName = null;

    #[ORM\Column(length: 50, unique: true, nullable: true)]
    private ?string $receiptNumber = null;

    public function __construct()
    {
        $this->cuotas = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Cuotas>
     */
    public function getCuotas(): Collection
    {
        return $this->cuotas;
    }

    public function addCuota(Cuotas $cuota): static
    {
        if (!$this->cuotas->contains($cuota)) {
            $this->cuotas->add($cuota);
            $cuota->setVenta($this);
        }
        return $this;
    }

    public function removeCuota(Cuotas $cuota): static
    {
        if ($this->cuotas->removeElement($cuota)) {
            if ($cuota->getVenta() === $this) {
                $cuota->setVenta(null);
            }
        }
        return $this;
    }

    public function getNumberOfInstallments(): ?int
    {
        return $this->numberOfInstallments;
    }

    public function setNumberOfInstallments(?int $numberOfInstallments): static
    {
        $this->numberOfInstallments = $numberOfInstallments;

        return $this;
    }

    /**
     * Verifica si hay alguna cuota pendiente cuya fecha de vencimiento ya pasó.
     */
    public function isPaymentOverdue(): bool
    {
        $today = new \DateTime('today');
        foreach ($this->cuotas as $cuota) {
            if ($cuota->getStatus() === 'Pendiente' && $cuota->getDueDate() < $today) {
                return true; // Encontramos una cuota atrasada
            }
        }
        return false; // Todas las cuotas pendientes están al día
    }

    /**
     * Calcula el monto total que ya ha sido pagado.
     */
    public function getTotalPaid(): float
    {
        $total = 0;
        foreach ($this->cuotas as $cuota) {
            if ($cuota->getStatus() === 'Pagada') {
                $total += $cuota->getAmount();
            }
        }
        return $total;
    }

    /**
     * Calcula el saldo pendiente de pago.
     */
    public function getPendingBalance(): float
    {
        return $this->getFinalSalePrice() - $this->getTotalPaid();
    }

    public function setSaleDocumentFile(?File $saleDocumentFile = null): void
    {
        $this->saleDocumentFile = $saleDocumentFile;
        if (null !== $saleDocumentFile) {
            // Es necesario para que el bundle sepa que hubo un cambio
            $this->setUpdatedAt(new \DateTimeImmutable());
        }
    }

    public function getSaleDocumentFile(): ?File
    {
        return $this->saleDocumentFile;
    }

    public function setSaleDocumentName(?string $saleDocumentName): void
    {
        $this->saleDocumentName = $saleDocumentName;
    }

    public function getSaleDocumentName(): ?string
    {
        return $this->saleDocumentName;
    }

    public function getReceiptNumber(): ?string
    {
        return $this->receiptNumber;
    }

    public function setReceiptNumber(?string $receiptNumber): static
    {
        $this->receiptNumber = $receiptNumber;
        return $this;
    }
}
