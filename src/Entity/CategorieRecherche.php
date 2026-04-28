<?php

namespace App\Entity;

class CategorieRecherche
{
    /**
     * @var string|null
     */
    private $libelle;

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

}
