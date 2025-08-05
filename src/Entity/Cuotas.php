<?php

namespace App\Entity;

use App\Repository\CuotasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: CuotasRepository::class)]
#[Vich\Uploadable]
class Cuotas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cuotas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ventas $venta = null;

    #[ORM\Column]
    private ?int $installmentNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'Pendiente';

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $paymentDate = null;

    #[Vich\UploadableField(mapping: 'receipt_files', fileNameProperty: 'receiptName')]
    private ?File $receiptFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $receiptName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getVenta(): ?Ventas { return $this->venta; }
    public function setVenta(?Ventas $venta): static { $this->venta = $venta; return $this; }
    public function getInstallmentNumber(): ?int { return $this->installmentNumber; }
    public function setInstallmentNumber(int $installmentNumber): static { $this->installmentNumber = $installmentNumber; return $this; }
    public function getAmount(): ?string { return $this->amount; }
    public function setAmount(string $amount): static { $this->amount = $amount; return $this; }
    public function getDueDate(): ?\DateTimeInterface { return $this->dueDate; }
    public function setDueDate(\DateTimeInterface $dueDate): static { $this->dueDate = $dueDate; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getPaymentDate(): ?\DateTimeInterface { return $this->paymentDate; }
    public function setPaymentDate(?\DateTimeInterface $paymentDate): static { $this->paymentDate = $paymentDate; return $this; }
    
    public function setReceiptFile(?File $receiptFile = null): void
    {
        $this->receiptFile = $receiptFile;
        if (null !== $receiptFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getReceiptFile(): ?File { return $this->receiptFile; }
    public function setReceiptName(?string $receiptName): void { $this->receiptName = $receiptName; }
    public function getReceiptName(): ?string { return $this->receiptName; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}