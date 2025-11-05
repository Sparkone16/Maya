<?php

namespace App\Entity;

use App\Repository\SaisonnierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SaisonnierRepository::class)]
class Saisonnier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 120,
        minMessage: 'Le nom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères',
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 120,
        minMessage: 'Le prénom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères',
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 10,
        minMessage: 'Le téléphone doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères',
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le mail est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le mail doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Le mail ne peut pas dépasser {{ limit }} caractères',
    )]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'L\'adresse doit comporter au moins {{ limit }} caractères',
        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères',
    )]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de naissance est obligatoire')]
    private ?\DateTime $dateNaissance = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date début est obligatoire')]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le salaire est obligatoire')]
    private ?float $salaireHoraire = null;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

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

    public function getDateNaissance(): ?\DateTime
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTime $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getSalaireHoraire(): ?float
    {
        return $this->salaireHoraire;
    }

    public function setSalaireHoraire(float $salaireHoraire): static
    {
        $this->salaireHoraire = $salaireHoraire;

        return $this;
    }
}
