<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection; 
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ColisRepository")
 */
class Colis
{ 
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */ 
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numeroColis;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomDestinataire;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenomDestinataire;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adresseDestinataire; 

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numeroAdresseDestinataire;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $noteColis;

    /**
     * @ORM\Column(type="boolean")
     */
    private $typeColis;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CodePostal", inversedBy="colis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $codePostal;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SuiviColis", mappedBy="colis")
     */
    private $suiviColis;

    /**
     * @ORM\Column(type="boolean")
     */
    private $expressColis;
    
    public function __construct()
    {
        $this->suiviColis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroColis(): ?string
    {
        return $this->numeroColis;
    }

    public function setNumeroColis(string $numeroColis): self
    {
        $this->numeroColis = $numeroColis;

        return $this;
    }

    public function getNomDestinataire(): ?string
    {
        return $this->nomDestinataire;
    }

    public function setNomDestinataire(string $nomDestinataire): self
    {
        $this->nomDestinataire = $nomDestinataire;

        return $this;
    }

    public function getPrenomDestinataire(): ?string
    {
        return $this->prenomDestinataire;
    }

    public function setPrenomDestinataire(?string $prenomDestinataire): self
    {
        $this->prenomDestinataire = $prenomDestinataire;

        return $this;
    }

    public function getAdresseDestinataire(): ?string
    {
        return $this->adresseDestinataire;
    }

    public function setAdresseDestinataire(string $adresseDestinataire): self
    {
        $this->adresseDestinataire = $adresseDestinataire;

        return $this;
    }

    public function getNumeroAdresseDestinataire(): ?string
    {
        return $this->numeroAdresseDestinataire;
    }

    public function setNumeroAdresseDestinataire(string $numeroAdresseDestinataire): self
    {
        $this->numeroAdresseDestinataire = $numeroAdresseDestinataire;

        return $this;
    }

    public function getNoteColis(): ?string
    {
        return $this->noteColis;
    }

    public function setNoteColis(?string $noteColis): self
    {
        $this->noteColis = $noteColis;

        return $this;
    }

    public function getTypeColis(): ?bool
    {
        return $this->typeColis;
    }

    public function setTypeColis(bool $typeColis): self
    {
        $this->typeColis = $typeColis;

        return $this;
    }

    public function getCodePostal(): ?CodePostal
    {
        return $this->codePostal;
    }

    public function setCodePostal(?CodePostal $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection|SuiviColis[]
     */
    public function getSuiviColis(): Collection
    {
        return $this->suiviColis;
    }

    public function addSuiviColis(SuiviColis $suiviColis): self
    {
        if (!$this->suiviColis->contains($suiviColis)) {
            $this->suiviColis[] = $suiviColis;
            $suiviColis->setColis($this);
        }

        return $this;
    }

    public function removeSuiviColis(SuiviColis $suiviColis): self
    {
        if ($this->suiviColis->contains($suiviColis)) {
            $this->suiviColis->removeElement($suiviColis);
            // set the owning side to null (unless already changed)
            if ($suiviColis->getColis() === $this) {
                $suiviColis->setColis(null);
            }
        }

        return $this;
    }

    public function getExpressColis(): ?bool
    {
        return $this->expressColis;
    }

    public function setExpressColis(bool $expressColis): self
    {
        $this->expressColis = $expressColis;

        return $this;
    }
}
