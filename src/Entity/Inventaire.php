<?php

namespace App\Entity;

use App\Repository\InventaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventaireRepository::class)]
class Inventaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $update_at = null;

    #[ORM\Column(length: 255)]
    #[ORM\JoinColumn(nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $stockinventaire = null;

    #[ORM\ManyToOne(inversedBy: 'inventaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $reference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $stockutiliser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->update_at;
    }

    public function setUpdateAt(\DateTimeInterface $update_at): static
    {
        $this->update_at = $update_at;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getStockinventaire(): ?string
    {
        return $this->stockinventaire;
    }

    public function setStockinventaire(string $stockinventaire): static
    {
        $this->stockinventaire = $stockinventaire;

        return $this;
    }

    public function getReference(): ?Produit
    {
        return $this->reference;
    }

    public function setReference(?Produit $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStockutiliser(): ?string
    {
        return $this->stockutiliser;
    }

    public function setStockutiliser(string $stockutiliser): static
    {
        $this->stockutiliser = $stockutiliser;

        return $this;
    }

    public function calculerEcart(): ?string
    {
        if ($this->reference === null) {
            return null;
        }

        $quantiteProduit = $this->reference->quantite();
        $ecart = $quantiteProduit - ($this->stockinventaire + $this->stockutiliser);
        return (string) $ecart;
    }
}
