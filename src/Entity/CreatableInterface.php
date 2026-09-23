<?php

namespace App\Entity;

/** Entidad que registra quien la creo y cuando. Lo completa AuditoriaListener. */
interface CreatableInterface
{
    public function getCreatedAt(): ?\DateTimeImmutable;

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static;

    public function getCreatedBy(): ?User;

    public function setCreatedBy(?User $createdBy): static;
}
