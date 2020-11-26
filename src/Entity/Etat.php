<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EtatRepository")
 * 
 * @UniqueEntity("codeEtat")
 */
class Etat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Type("digit")
     * @Assert\Length(min = 3, max = 3)
     */
    private $codeEtat;

    /**
     * @ORM\Column(type="text")
     * 
     * @Assert\Length(min = 2, max = 150)
     */
    private $descriptifEtat;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SuiviColis", mappedBy="etat")
     */
    private $suiviColis;

    public function __construct()
    {
        $this->suiviColis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeEtat(): ?string
    {
        return $this->codeEtat;
    }

    public function setCodeEtat(string $codeEtat): self
    {
        $this->codeEtat = $codeEtat;

        return $this;
    }

    public function getDescriptifEtat(): ?string
    {
        return $this->descriptifEtat;
    }

    public function setDescriptifEtat(string $descriptifEtat): self
    {
        $this->descriptifEtat = $descriptifEtat;

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
            $suiviColis->setEtat($this);
        }

        return $this;
    }

    public function removeSuiviColi(SuiviColis $suiviColis): self
    {
        if ($this->suiviColis->contains($suiviColis)) {
            $this->suiviColis->removeElement($suiviColis);
            // set the owning side to null (unless already changed)
            if ($suiviColis->getEtat() === $this) {
                $suiviColis->setEtat(null);
            }
        }

        return $this;
    }
}
