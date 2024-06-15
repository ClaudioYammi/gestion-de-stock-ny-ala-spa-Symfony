<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datecommande = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $numfacture = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $idVille = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $idClient = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $Tva = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $Remise = null;

    #[ORM\Column]
    private ?bool $etatcommande = null;

    /**
     * @var Collection<int, DetailCommande>
     */
    #[ORM\OneToMany(mappedBy: 'idCommande', targetEntity: DetailCommande::class)]
    private Collection $detailCommandes;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->detailCommandes = new ArrayCollection();

        $timezone = new \DateTimeZone('Asia/Jerusalem'); // Replace with your desired timezone offset (+03:00)
        $now = new \DateTime('now', $timezone);
        $this->created_at = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d H:i:s'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatecommande(): ?\DateTimeInterface
    {
        return $this->datecommande;
    }

    public function setDatecommande(\DateTimeInterface $datecommande): static
    {
        $this->datecommande = $datecommande;

        return $this;
    }

    public function getIdVille(): ?Ville
    {
        return $this->idVille;
    }

    public function getNumfacture(): ?string
    {
        return $this->numfacture;
    }

    public function setNumfacture(string $numfacture): static
    {
        $this->numfacture = $numfacture;

        return $this;
    }

    public function setIdVille(?Ville $idVille): static
    {
        $this->idVille = $idVille;

        return $this;
    }

    public function getIdClient(): ?Client
    {
        return $this->idClient;
    }

    public function setIdClient(?Client $idClient): static
    {
        $this->idClient = $idClient;

        return $this;
    }

    public function isEtatcommande(): ?bool
    {
        return $this->etatcommande;
    }

    public function setEtatcommande(bool $etatcommande): static
    {
        $this->etatcommande = $etatcommande;

        return $this;
    }

    /**
     * @return Collection<int, DetailCommande>
     */
    public function getDetailCommandes(): Collection
    {
        return $this->detailCommandes;
    }

    public function addDetailCommande(DetailCommande $detailCommande): static
    {
        if (!$this->detailCommandes->contains($detailCommande)) {
            $this->detailCommandes->add($detailCommande);
            $detailCommande->setIdCommande($this);
        }

        return $this;
    }

    public function removeDetailCommande(DetailCommande $detailCommande): static
    {
        if ($this->detailCommandes->removeElement($detailCommande)) {
            // set the owning side to null (unless already changed)
            if ($detailCommande->getIdCommande() === $this) {
                $detailCommande->setIdCommande(null);
            }
        }

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

    public function getTva(): ?string
    {
        return $this->Tva;
    }

    public function setTva(string $Tva): static
    {
        $this->Tva = $Tva;
        
        return $this;
    }

    public function getRemise(): ?string
    {
        return $this->Remise;
    }
    
    public function setRemise(string $Remise): static
    {
        $this->Remise = $Remise;

        return $this;
    }

    public function soustotal(): float
    {
        $soustotal = 0.0;
        foreach ($this->getDetailCommandes() as $detailAchat) {
            $soustotal += $detailAchat->getPrixunitaire() * $detailAchat->getQuantite();
        }
        return $soustotal;
    }

    public function total(): float
    {
        $total = 0.0;
        $tva = 0.0;
        $remise = 0.0;

        foreach ($this->getDetailCommandes() as $detailAchat) {
            $montant = $detailAchat->getPrixunitaire() * $detailAchat->getQuantite();
            $tva += $montant * ($this->getTva() / 100);
            $remise += $montant * ($this->getRemise() / 100);
            $total += $montant;
        }

        $total += $tva;
        $total -= $remise;

        return $total;
    }
}
