<?php

namespace App\Entity;

use App\Repository\DetailAchatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailAchatRepository::class)]
class DetailAchat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'detailAchats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $reference = null;

    #[ORM\ManyToOne(inversedBy: 'detailAchats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Achat $idAchat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixunitaire = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdAchat(): ?Achat
    {
        return $this->idAchat;
    }

    public function setIdAchat(?Achat $idAchat): static
    {
        $this->idAchat = $idAchat;

        return $this;
    }

    public function getQuantite(): ?string
    {
        return $this->quantite;
    }

    public function setQuantite(string $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrixunitaire(): ?string
    {
        return $this->prixunitaire;
    }

    public function setPrixunitaire(string $prixunitaire): static
    {
        $this->prixunitaire = $prixunitaire;

        return $this;
    }

    /**
     * Calculates the total amount of all DetailAchat entities in the repository.
     *
     * @return float The total amount of all DetailAchat entities.
     */
    public static function getTotalDesAchats(DetailAchatRepository $detailAchatRepository): float
    {
        $qb = $detailAchatRepository->createQueryBuilder('da');
        $qb->select('SUM(da.quantite * da.prixunitaire)');

        return (float) $qb->getQuery()->getSingleScalarResult();
    }
}


