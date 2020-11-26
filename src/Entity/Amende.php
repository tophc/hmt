<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AmendeRepository")
 */
class Amende
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
 
    /**
     * @ORM\Column(type="datetime")
     * 
     * @Assert\Type("object")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $dateAmende;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Type(type={"digit"})
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $numAmende;

    /**
     * @ORM\Column(type="float")
     * 
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $montantAmende;

    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @Assert\Length(min = 2, max = 150)
     */
    private $remarqueAmende;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Vehicule", inversedBy="amendes")
     * 
     * @Assert\Type("App\Entity\Vehicule")
     * @Assert\NotNull
     * @Assert\NotBlank 
     */
    private $vehicule;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAmende(): ?\DateTimeInterface
    {
        return $this->dateAmende;
    }
 
    public function setDateAmende(\DateTimeInterface $dateAmende): self
    {
        $this->dateAmende = $dateAmende;

        return $this;
    }

    public function getNumAmende(): ?string
    {
        return $this->numAmende;
    }

    public function setNumAmende(string $numAmende): self
    {
        $this->numAmende = $numAmende;

        return $this;
    }

    public function getMontantAmende(): ?float
    {
        return $this->montantAmende;
    }

    public function setMontantAmende(float $montantAmende): self
    {
        $this->montantAmende = $montantAmende;

        return $this;
    }

    public function getRemarqueAmende(): ?string
    {
        return $this->remarqueAmende;
    }

    public function setRemarqueAmende(string $remarqueAmende): self
    {
        $this->remarqueAmende = $remarqueAmende;

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
}
