<?php

namespace App\Entity;

use DateTime;

class FournisseurRecherche
{
    /**
     * @var string|null
     */
    private $nom;
    /**
     * @var string |null
     */
    private $prenom;
    /**
     * @var string|null
     */
    private $adresse;
    /**
     * @var mail|null
     */
    private $mail;

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $libelle
     */
    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $libelle
     */
    public function setPrenom(?string $prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * @return string|null
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * @param string|null $adresse
     */
    public function setAdresse(?string $adresse): void
    {
        $this->adresse = $adresse;
    }

    /**
     * @return string|null
     */
    public function getMail(): ?string
    {
        return $this->mail;
    }

    /**
     * @param string|null $mail
     */
    public function setDateMaxi(?string $mail): void
    {
        $this->mail = $mail;
    }

}