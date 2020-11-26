<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ControleTechniqueRepository")
 */
class ControleTechnique
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
    private $dateControleTechnique;

    /**
     * @ORM\Column(type="boolean")
     * 
     * @Assert\Type("bool")
     */
    private $statutControleTechnique;

    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @Assert\Length(min = 2, max = 150)
     */
    private $remarqueControleTechnique;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Vehicule", inversedBy="controleTechniques")
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

    public function getDateControleTechnique(): ?\DateTimeInterface
    {
        return $this->dateControleTechnique;
    }

    public function setDateControleTechnique(\DateTimeInterface $dateControleTechnique): self
    {
        $this->dateControleTechnique = $dateControleTechnique;

        return $this;
    }

    public function getStatutControleTechnique(): ?bool
    {
        return $this->statutControleTechnique;
    }

    public function setStatutControleTechnique(bool $statutControleTechnique): self
    {
        $this->statutControleTechnique = $statutControleTechnique;

        return $this;
    }

    public function getRemarqueControleTechnique(): ?string
    {
        return $this->remarqueControleTechnique;
    }

    public function setRemarqueControleTechnique(?string $remarqueControleTechnique): self
    {
        $this->remarqueControleTechnique = $remarqueControleTechnique;

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

    // Méthode ajoutées manuellement 

    public function eraseDateControleTechnique(): self
    {
        $this->dateControleTechnique = null;

        return $this;
    }
}
