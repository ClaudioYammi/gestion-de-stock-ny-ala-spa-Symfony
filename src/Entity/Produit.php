<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $designation = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixunitaire = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateexp = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $qttemin = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $qttemax = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $idCategorie = null;

    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: DetailVente::class)]
    private Collection $detailVentes;

    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: DetailAchat::class, orphanRemoval: true)]
    private Collection $detailAchats;

    /**
     * @var Collection<int, DetailCommande>
     */
    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: DetailCommande::class)]
    private Collection $detailCommandes;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emplacement $emplacement = null;

   
    #[ORM\Column(length: 255)]
    private ?string $capaciter = null;

    #[ORM\Column(length: 255)]
    private ?string $unite = null;

    /**
     * @var Collection<int, Inventaire>
     */
    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: Inventaire::class)]
    private Collection $inventaires;

    #[ORM\Column(type: "string")]
    private $imageName;

    #[Vich\UploadableField(mapping: "product_images", fileNameProperty: "imageName")]
    private $imageFile;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $updatedAt;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixunitairevente = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $reference_produit = null;
    
    public function __construct()
    {
        $this->detailVentes = new ArrayCollection();
        $this->detailAchats = new ArrayCollection();
        $this->detailCommandes = new ArrayCollection();
        $this->inventaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getDateexp(): ?\DateTimeInterface
    {
        return $this->dateexp;
    }

    public function setDateexp(\DateTimeInterface $dateexp): static
    {
        $this->dateexp = $dateexp;

        return $this;
    }

    public function getQttemin(): ?string
    {
        return $this->qttemin;
    }

    public function setQttemin(string $qttemin): static
    {
        $this->qttemin = $qttemin;

        return $this;
    }

    public function getQttemax(): ?string
    {
        return $this->qttemax;
    }

    public function setQttemax(string $qttemax): static
    {
        $this->qttemax = $qttemax;

        return $this;
    }

    

    public function getIdCategorie(): ?Categorie
    {
        return $this->idCategorie;
    }

    public function setIdCategorie(?Categorie $idCategorie): static
    {
        $this->idCategorie = $idCategorie;

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
            $detailVente->setReference($this);
        }

        return $this;
    }

    public function removeDetailVente(DetailVente $detailVente): static
    {
        if ($this->detailVentes->removeElement($detailVente)) {
            // set the owning side to null (unless already changed)
            if ($detailVente->getReference() === $this) {
                $detailVente->setReference(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, DetailAchat>
     */
    public function getDetailAchats(): Collection
    {
        return $this->detailAchats;
    }
    
    public function addDetailAchat(DetailAchat $detailAchat): static
    {
        if (!$this->detailAchats->contains($detailAchat)) {
            $this->detailAchats->add($detailAchat);
            $detailAchat->setReference($this);
        }
        
        return $this;
    }
    
    public function removeDetailAchat(DetailAchat $detailAchat): static
    {
        if ($this->detailAchats->removeElement($detailAchat)) {
            // set the owning side to null (unless already changed)
            if ($detailAchat->getReference() === $this) {
                $detailAchat->setReference(null);
            }
        }
        
        return $this;
    }

    // public function soustraireQuantiteStock(int $quantite): void
    // {
    //     $quantiteStockActuelle = $this->getQuantitestock();
        
    //     if ($quantiteStockActuelle >= $quantite) {
    //         $nouvelleQuantiteStock = $quantiteStockActuelle - $quantite;
    //         $this->setQuantitestock($nouvelleQuantiteStock);
    //     } else {
    //         throw new \LogicException('La quantité demandée est supérieure à la quantité en stock.');
    //     }
    // }

    // public function ajouterQuantiteStock(int $quantite): void
    // {
    //     if ($quantite < 0) {
    //         throw new \InvalidArgumentException('La quantité doit être un nombre positif.');
    //     }

    //     $quantiteStockActuelle = $this->getQuantiteStock();
    //     $nouvelleQuantiteStock = $quantiteStockActuelle + $quantite;

    //     // Vérifie si la quantité résultante dépasse une limite maximale (facultatif)
    //     $quantiteMaximale = 1000; // Remplacez par la limite maximale souhaitée
    //     if ($nouvelleQuantiteStock > $quantiteMaximale) {
    //         throw new \RuntimeException('La quantité résultante dépasse la limite maximale.');
    //     }

    //     $this->setQuantiteStock($nouvelleQuantiteStock);
    // }

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
            $detailCommande->setReference($this);
        }

        return $this;
    }

    public function removeDetailCommande(DetailCommande $detailCommande): static
    {
        if ($this->detailCommandes->removeElement($detailCommande)) {
            // set the owning side to null (unless already changed)
            if ($detailCommande->getReference() === $this) {
                $detailCommande->setReference(null);
            }
        }

        return $this;
    }

    public function quantite(): int
    {
        
        return $this->getQuantiteAchat()-$this->getQuantiteVente()-$this->getQuantiteCommande();//+$this->getQuantiteInventaire();

    }

    
    public function getQuantiteAchat(): int //alaina ny qtt novidiny rehetra
    {
      
        $detailAchats = $this->getDetailAchats();

        $quantiteAchat = 0;
        foreach ($detailAchats as $detailAchat) {
            $quantiteAchat += $detailAchat->getQuantite();
        }

        return $quantiteAchat;
    }

    public function getQuantiteVente(): int // qtte lafo
    {
        
        $detailVentes = $this->getDetailVentes();

        $quantiteVente = 0;
        foreach ($detailVentes as $detailVente) {
            $quantiteVente += $detailVente->getQuantite();
        }

        return $quantiteVente;
    }

    public function getQuantiteCommande(): int // qtte lafo
    {
        
        $detailCommandes = $this->getDetailCommandes();

        $quantiteCommande = 0;
        foreach ($detailCommandes as $detailCommande) {
            $quantiteCommande += $detailCommande->getQuantite();
        }

        return $quantiteCommande;
    }

    public function getEmplacement(): ?Emplacement
    {
        return $this->emplacement;
    }

    public function setEmplacement(?Emplacement $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    public function getCapaciter(): ?string
    {
        return $this->capaciter;
    }

    public function setCapaciter(string $capaciter): static
    {
        $this->capaciter = $capaciter;

        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    /**
     * @return Collection<int, Inventaire>
     */
    public function getInventaires(): Collection
    {
        return $this->inventaires;
    }

    public function addInventaire(Inventaire $inventaire): static
    {
        if (!$this->inventaires->contains($inventaire)) {
            $this->inventaires->add($inventaire);
            $inventaire->setReference($this);
        }

        return $this;
    }

    public function removeInventaire(Inventaire $inventaire): static
    {
        if ($this->inventaires->removeElement($inventaire)) {
            // set the owning side to null (unless already changed)
            if ($inventaire->getReference() === $this) {
                $inventaire->setReference(null);
            }
        }

        return $this;
    }

    public function getImageName(): ?string {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self {
        $this->imageName = $imageName;
        return $this;
    }

    public function getImageFile() {
        return $this->imageFile;
    }

    public function setImageFile($imageFile): self {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTime();
        }
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self {
        $this->updatedAt = $updatedAt;
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

    public function getReferenceProduit(): ?string
    {
        return $this->reference_produit;
    }

    public function setReferenceProduit(?string $reference_produit): static
    {
        $this->reference_produit = $reference_produit;

        return $this;
    }
}
