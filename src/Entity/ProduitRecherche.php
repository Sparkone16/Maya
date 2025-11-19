<?php

namespace App\Entity;

class ProduitRecherche
{
    /**
     * @var string|null
     */
    private $libelle;
    /**
     * @var float|null
     */
    private $prixMini;
    /**
     * @var float|null
     */
    private $prixMaxi;
    /**
     * @var Categorie|null
     */
    private $categorie;
    /**
     * @var int|null
     */
    private $categorieId;

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string|null $libelle
     */
    public function setLibelle(?string $libelle): void
    {
        $this->libelle = $libelle;
    }

    /**
     * @return float|null
     */
    public function getPrixMini(): ?float
    {
        return $this->prixMini;
    }

    /**
     * @param float|null $prixMini
     */
    public function setPrixMini(?float $prixMini): void
    {
        $this->prixMini = $prixMini;
    }

    /**
     * @return float|null
     */
    public function getPrixMaxi(): ?float
    {
        return $this->prixMaxi;
    }

    /**
     * @param float|null $prixMaxi
     */
    public function setPrixMaxi(?float $prixMaxi): void
    {
        $this->prixMaxi = $prixMaxi;
    }

    /**
     * @return Categorie|null
     */
    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    /**
     * @param float|null $prixMaxi
     */
    public function setCategorie(?Categorie $categorie): void
    {
        $this->categorie = $categorie;
    }

    
    public function getCategorieId(): ?int {
        return $this->categorieId;
    }

    public function setCategorieId(?int $id): void {
        $this->categorieId = $id;
    }

}
