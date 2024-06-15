<?php

namespace App\Entity;

use App\Repository\VenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datevente = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $numfacture = null;

    #[ORM\ManyToOne(inversedBy: 'ventes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $idClient = null;

    #[ORM\OneToMany(mappedBy: 'idVente', targetEntity: DetailVente::class)]
    private Collection $detailVentes;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $Tva = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $Remise = null;
    
    public function __construct()
    {
        $this->detailVentes = new ArrayCollection();

        $timezone = new \DateTimeZone('Asia/Jerusalem'); // Replace with your desired timezone offset (+03:00)
        $now = new \DateTime('now', $timezone);
        $this->created_at = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d H:i:s'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatevente(): ?\DateTimeInterface
    {
        return $this->datevente;
    }

    public function setDatevente(\DateTimeInterface $datevente): static
    {
        $this->datevente = $datevente;

        return $this;
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

    public function getIdClient(): ?Client
    {
        return $this->idClient;
    }

    public function setIdClient(?Client $idClient): static
    {
        $this->idClient = $idClient;

        return $this;
    }

    /**
     * @return Collection<int, DetailVente>
     */
    public function getDetailVentes(): Collection
    {
        return $this->detailVentes;
    }

    public function addDetailVente(DetailVente $detailVente): static
    {
        if (!$this->detailVentes->contains($detailVente)) {
            $this->detailVentes->add($detailVente);
            $detailVente->setIdVente($this);
        }

        return $this;
    }

    public function removeDetailVente(DetailVente $detailVente): static
    {
        if ($this->detailVentes->removeElement($detailVente)) {
            // set the owning side to null (unless already changed)
            if ($detailVente->getIdVente() === $this) {
                $detailVente->setIdVente(null);
            }
        }

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function soustotal(): float
    {
    $soustotal = 0.0;

        foreach ($this->getDetailVentes() as $detailVente) {
            $soustotal += $detailVente->getPrixunitairevente() * $detailVente->getQuantite();
        }
        return $soustotal;
    }

    public function total(): float
    {
        $total = 0.0;
        $tva = 0.0;
        $remise = 0.0;

        foreach ($this->getDetailVentes() as $detailVente) {
            $montant = $detailVente->getPrixunitairevente() * $detailVente->getQuantite();
            $tva += $montant * ($this->getTva() / 100);
            $remise += $montant * ($this->getRemise() / 100);
            $total += $montant;
        }

        $total += $tva;
        $total -= $remise;

        return $total;
    }
}
