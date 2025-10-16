<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: 'Le nom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères',
    )]

    private ?string $nom = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: 'Le prénom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères',
    )]

    private ?string $prenom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'L\'adresse doit comporter au moins {{ limit }} caractères',
        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères',
    )]

    private ?string $adresse = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\NotBlank(message: 'Le mail est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le mail doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le mail ne peut pas dépasser {{ limit }} caractères',
    )]

    private ?string $mail = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
    #[Assert\Length(
        min: 0,
        max: 10,
        minMessage: 'Le téléphone doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères',
    )]

    private ?string $telephone = null;

    #[ORM\Column]
    private ?\DateTime $dateEntreeRel = null;

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDateEntreeRel(): ?\DateTime
    {
        return $this->dateEntreeRel;
    }

    public function setDateEntreeRel(\DateTime $dateEntreeRel): static
    {
        $this->dateEntreeRel = $dateEntreeRel;

        return $this;
    }
}
