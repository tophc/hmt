<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TourneeRepository")
 * 
 * @UniqueEntity("numTournee")
 */
class Tournee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */ 
    private $id;

    /** 
     * @ORM\Column(type="string", length=3) 
     * 
     * @Assert\Type(type={"digit"})
     * @Assert\Length(min = 3, max = 3)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $numTournee;

    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @Assert\Length(min = 5, max = 150)
     */
    private $infoTournee;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Affectation", mappedBy="tournee")
     */
    private $affectations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CodePostal", mappedBy="tournee")
     * @ORM\OrderBy({"numCodePostal" = "ASC"})
     */
    private $codePostals;

    public function __construct()
    {
        $this->affectations = new ArrayCollection();
        $this->codePostals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumTournee(): ?string
    {
        return $this->numTournee;
    }

    public function setNumTournee(string $numTournee): self
    {
        $this->numTournee = $numTournee;

        return $this;
    }

    /**
     * @return Collection|Affectation[]
     */
    public function getAffectations(): Collection
    {
        return $this->affectations;
    }

    public function addAffectation(Affectation $affectation): self
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations[] = $affectation;
            $affectation->setTournee($this);
        }

        return $this;
    }

    public function removeAffectation(Affectation $affectation): self
    {
        if ($this->affectations->contains($affectation)) {
            $this->affectations->removeElement($affectation);
            // set the owning side to null (unless already changed)
            if ($affectation->getTournee() === $this) {
                $affectation->setTournee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CodePostal[]
     */
    public function getCodePostals(): Collection
    {
        return $this->codePostals;
    }

    public function addCodePostal(CodePostal $codePostal): self
    {
        if (!$this->codePostals->contains($codePostal)) {
            $this->codePostals[] = $codePostal;
            $codePostal->setTournee($this);
        }

        return $this;
    }

    public function removeCodePostal(CodePostal $codePostal): self
    {
        if ($this->codePostals->contains($codePostal)) {
            $this->codePostals->removeElement($codePostal);
            // set the owning side to null (unless already changed)
            if ($codePostal->getTournee() === $this) {
                $codePostal->setTournee(null);
            }
        }

        return $this;
    }

    public function getInfoTournee(): ?string
    {
        return $this->infoTournee;
    }

    public function setInfoTournee(?string $infoTournee): self
    {
        $this->infoTournee = $infoTournee;

        return $this;
    }

}
