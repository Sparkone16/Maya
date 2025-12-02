<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[Vich\Uploadable]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le libellé doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le libellé ne peut pas dépasser {{ limit }} caractères',
    )]

    private ?string $libelle = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'categorie')]
    private Collection $produits;

    #[Vich\UploadableField(mapping: 'categories', fileNameProperty: 'imageNom', size: 'imageTaille')]
    private ?File $imageFichier = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageNom = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageTaille = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $imageDateMaj = null;


    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setCategorie($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getCategorie() === $this) {
                $produit->setCategorie(null);
            }
        }

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFichier(?File $imageFichier = null): void
    {
        $this->imageFichier = $imageFichier;

        if (null !== $imageFichier) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->imageDateMaj = new \DateTimeImmutable();
        }
    }
   
    public function getImageFichier(): ?File
    {
        return $this->imageFichier;
    }

    public function setImageNom(?string $imageNom): void
    {
        $this->imageNom = $imageNom;
    }

    public function getImageNom(): ?string
    {
        return $this->imageNom;
    }

    public function setImageTaille(?int $imageTaille): void
    {
        $this->imageTaille = $imageTaille;
    }

    public function getImageTaille(): ?int
    {
        return $this->imageTaille;
    }

}
