<?php

namespace App\Entity;

use App\Repository\DetailVenteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailVenteRepository::class)]
class DetailVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'detailVentes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $reference = null;

    #[ORM\ManyToOne(inversedBy: 'detailVentes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vente $idVente = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixunitairevente = null;
    

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

    public function getIdVente(): ?Vente
    {
        return $this->idVente;
    }

    public function setIdVente(?Vente $idVente): static
    {
        $this->idVente = $idVente;

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

    public function getPrixunitairevente(): ?string
    {
        return $this->prixunitairevente;
    }

    public function setPrixunitairevente(string $prixunitairevente): static
    {
        $this->prixunitairevente = $prixunitairevente;

        return $this;
    }

    /**
     * Calculates the total amount of all DetailAchat entities in the repository.
     *
     * @return float The total amount of all DetailAchat entities.
     */
    public static function getTotalDesVentes(DetailVenteRepository $detailVenteRepository): float
    {
        $qb = $detailVenteRepository->createQueryBuilder('da');
        $qb->select('SUM(da.quantite * da.prixunitairevente)');

        return (float) $qb->getQuery()->getSingleScalarResult();
    }
}
