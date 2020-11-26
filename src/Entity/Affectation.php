<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AffectationRepository")
 * 
 */
class Affectation 
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;  

    /**
     * @ORM\Column(type="date")
     * 
     * @Assert\Type("object")
     * @Assert\NotNull
     * @Assert\NotBlank 
     */
    private $dateAffectation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Chauffeur", inversedBy="affectations")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\Type("App\Entity\Chauffeur")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $chauffeur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Vehicule", inversedBy="affectations")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\Type("App\Entity\Vehicule")
     * @Assert\NotNull
     * @Assert\NotBlank 
     */
    private $vehicule;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournee", inversedBy="affectations")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\Type("App\Entity\Tournee")
     * @Assert\NotNull
     * @Assert\NotBlank 
     */
    private $tournee;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAffectation(): ?\DateTimeInterface
    {
        return $this->dateAffectation;
    }

    public function setDateAffectation(\DateTimeInterface $dateAffectation): self
    {
        $this->dateAffectation = $dateAffectation;

        return $this;
    }

    public function getChauffeur(): ?Chauffeur
    {
        return $this->chauffeur;
    }

    public function setChauffeur(?Chauffeur $chauffeur): self
    {
        $this->chauffeur = $chauffeur;

        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicule $vehicule): self
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    public function getTournee(): ?Tournee
    {
        return $this->tournee;
    }

    public function setTournee(?Tournee $tournee): self
    {
        $this->tournee = $tournee;

        return $this;
    }
}
