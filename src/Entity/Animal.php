<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
#[Vich\Uploadable]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le nom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères',
    )]

    private ?string $nom = null;

    // #[ORM\Column(length: 255)]
    // #[Assert\NotBlank(message: 'La race est obligatoire')]
    // #[Assert\Length(
    //     min: 3,
    //     max: 50,
    //     minMessage: 'La race doit comporter au moins {{ limit }} caractères',
    //     maxMessage: 'La race ne peut pas dépasser {{ limit }} caractères',
    // )]

    // private ?string $race = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\ManyToOne(inversedBy: 'animals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RaceAnimal $raceAnimal = null;

    #[Vich\UploadableField(mapping: 'categories', fileNameProperty: 'imageNom', size: 'imageTaille')]
    private ?File $imageFichier = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageNom = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageTaille = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $imageDateMaj = null;

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

    // public function getRace(): ?string
    // {
    //     return $this->race;
    // }

    // public function setRace(string $race): static
    // {
    //     $this->race = $race;
    //     return $this;
    // }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getRaceAnimal(): ?RaceAnimal
    {
        return $this->raceAnimal;
    }

    public function setRaceAnimal(?RaceAnimal $raceAnimal): static
    {
        $this->raceAnimal = $raceAnimal;
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
