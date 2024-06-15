<?php

namespace App\Entity;

use App\Repository\FournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
class Fournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $telephone = null;

    #[ORM\OneToMany(mappedBy: 'idFournisseur', targetEntity: Achat::class)]
    private Collection $achats;

    

    public function __construct()
    {
        $this->achats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, Achat>
     */
    public function getAchats(): Collection
    {
        return $this->achats;
    }

    public function addAchat(Achat $Achat): static
    {
        if (!$this->achats->contains($Achat)) {
            $this->achats->add($Achat);
            $Achat->setIdFournisseur($this);
        }

        return $this;
    }

    public function removeAchat(Achat $achat): static
    {
        if ($this->achats->removeElement($achat)) {
            // set the owning side to null (unless already changed)
            if ($achat->getIdFournisseur() === $this) {
                $achat->setIdFournisseur(null);
            }
        }

        return $this;
    }
    
}
