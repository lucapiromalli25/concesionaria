<?php

namespace App\Entity;

/**
 * Los 7 campos de auditoria: status, created_by/at, updated_by/at, deleted_by/at.
 * La implementacion vive en App\Entity\Trait\Auditable.
 */
interface AuditableInterface extends CreatableInterface
{
    public const STATUS_ACTIVO = 'activo';
    public const STATUS_INACTIVO = 'inactivo';

    public function getStatus(): string;

    public function setStatus(string $status): static;

    public function estaActivo(): bool;

    public function getUpdatedAt(): ?\DateTimeImmutable;

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static;

    public function getUpdatedBy(): ?User;

    public function setUpdatedBy(?User $updatedBy): static;

    public function getDeletedAt(): ?\DateTimeImmutable;

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static;

    public function getDeletedBy(): ?User;

    public function setDeletedBy(?User $deletedBy): static;
}
