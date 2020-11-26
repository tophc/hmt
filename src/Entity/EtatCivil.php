<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EtatCivilRepository")
 */
class EtatCivil
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
    private $nomEtatCivil;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Chauffeur", mappedBy="etatCivilChauffeur")
     */
    private $chauffeurs;

    public function __construct()
    {
        $this->chauffeurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEtatCivil(): ?string
    {
        return $this->nomEtatCivil;
    }

    public function setNomEtatCivil(string $nomEtatCivil): self
    {
        $this->nomEtatCivil = $nomEtatCivil;

        return $this;
    }

    /**
     * @return Collection|Chauffeur[]
     */
    public function getChauffeurs(): Collection
    {
        return $this->chauffeurs;
    }

    public function addChauffeur(Chauffeur $chauffeur): self
    {
        if (!$this->chauffeurs->contains($chauffeur)) {
            $this->chauffeurs[] = $chauffeur; 
            $chauffeur->setEtatCivilChauffeur($this);
        }

        return $this;
    }

    public function removeChauffeur(Chauffeur $chauffeur): self
    {
        if ($this->chauffeurs->contains($chauffeur)) {
            $this->chauffeurs->removeElement($chauffeur);
            // set the owning side to null (unless already changed)
            if ($chauffeur->getEtatCivilChauffeur() === $this) {
                $chauffeur->setEtatCivilChauffeur(null);
            }
        }

        return $this;
    }
}
