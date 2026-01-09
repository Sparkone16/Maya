<?php

namespace App\Entity;

use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
#[Vich\Uploadable]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'recettes')]
    private Collection $produits;

    #[ORM\Column]
    private ?int $tempsPreparation = null;

    #[ORM\Column]
    private ?int $tempsCuisson = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $ingredient = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $estSucree = null;

    #[ORM\Column]
    private ?bool $estTraditionnelle = null;

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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        $this->produits->removeElement($produit);

        return $this;
    }

    public function getTempsPreparation(): ?int
    {
        return $this->tempsPreparation;
    }

    public function setTempsPreparation(int $tempsPreparation): static
    {
        $this->tempsPreparation = $tempsPreparation;

        return $this;
    }

    public function getTempsCuisson(): ?int
    {
        return $this->tempsCuisson;
    }

    public function setTempsCuisson(int $tempsCuisson): static
    {
        $this->tempsCuisson = $tempsCuisson;

        return $this;
    }

    public function getIngredient(): ?string
    {
        return $this->ingredient;
    }

    public function setIngredient(string $ingredient): static
    {
        $this->ingredient = $ingredient;

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

    public function isEstSucree(): ?bool
    {
        return $this->estSucree;
    }

    public function setEstSucree(bool $estSucree): static
    {
        $this->estSucree = $estSucree;

        return $this;
    }

    public function isEstTraditionnelle(): ?bool
    {
        return $this->estTraditionnelle;
    }

    public function setEstTraditionnelle(bool $estTraditionnelle): static
    {
        $this->estTraditionnelle = $estTraditionnelle;

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
