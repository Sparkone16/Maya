<?php

namespace App\Entity;

use App\Repository\OffreVolumeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreVolumeRepository::class)]
class OffreVolume
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'offreVolumes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $Produits = null;

    #[ORM\Column]
    private ?int $qteVolume = null;

    #[ORM\Column]
    private ?float $prix = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduits(): ?Produit
    {
        return $this->Produits;
    }

    public function setProduits(?Produit $Produits): static
    {
        $this->Produits = $Produits;

        return $this;
    }

    public function getQteVolume(): ?int
    {
        return $this->qteVolume;
    }

    public function setQteVolume(int $qteVolume): static
    {
        $this->qteVolume = $qteVolume;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }
}
