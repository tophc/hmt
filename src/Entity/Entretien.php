<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EntretienRepository")
 */
class Entretien
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
    private $dateEntretien;

    /**
     * @ORM\Column(type="integer")
     */
    private $kmEntretien;

    /**
     * @ORM\Column(type="float")
     * 
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $montantEntretien;

    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @Assert\Length(min = 2, max = 150)
     */
    private $remarqueEntretien;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Vehicule", inversedBy="entretiens")
     * @ORM\JoinColumn(nullable=false)
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

    public function getDateEntretien(): ?\DateTimeInterface
    {
        return $this->dateEntretien;
    }

    public function setDateEntretien(\DateTimeInterface $dateEntretien): self
    {
        $this->dateEntretien = $dateEntretien;

        return $this;
    }

    public function getKmEntretien(): ?int
    {
        return $this->kmEntretien;
    }

    public function setKmEntretien(int $kmEntretien): self
    {
        $this->kmEntretien = $kmEntretien;

        return $this;
    }

    public function getMontantEntretien(): ?float
    {
        return $this->montantEntretien;
    }

    public function setMontantEntretien(float $montantEntretien): self
    {
        $this->montantEntretien = $montantEntretien;

        return $this;
    }

    public function getRemarqueEntretien(): ?string
    {
        return $this->remarqueEntretien;
    }

    public function setRemarqueEntretien(?string $remarqueEntretien): self
    {
        $this->remarqueEntretien = $remarqueEntretien;

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
