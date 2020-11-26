<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CodePostalRepository")
 */
class CodePostal
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /** 
     * @ORM\Column(type="integer")
     * 
     * @Assert\NotNull
     * @Assert\NotBlank
     */ 
    private $numCodePostal;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $localiteCodePostal;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournee", inversedBy="codePostals")
     * @ORM\joinColumn(onDelete="SET NULL")
     */
    private $tournee;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Colis", mappedBy="codePostal" )
     */
    private $colis;

    public function __construct()
    {
        $this->colis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumCodePostal(): ?int
    {
        return $this->numCodePostal;
    }

    public function setNumCodePostal(int $numCodePostal): self
    {
        $this->numCodePostal = $numCodePostal;

        return $this;
    }

    public function getLocaliteCodePostal(): ?string
    {
        return $this->localiteCodePostal;
    }

    public function setLocaliteCodePostal(string $localiteCodePostal): self
    {
        $this->localiteCodePostal = $localiteCodePostal;

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

    /**
     * @return Collection|Colis[]
     */
    public function getColis(): Collection
    {
        return $this->colis;
    }

    public function addColis(Colis $colis): self
    {
        if (!$this->colis->contains($colis)) {
            $this->colis[] = $colis;
            $colis->setCodePostal($this);
        }

        return $this;
    }

    public function removeColis(Colis $colis): self
    {
        if ($this->colis->contains($colis)) {
            $this->colis->removeElement($colis);
            // set the owning side to null (unless already changed)
            if ($colis->getCodePostal() === $this) {
                $colis->setCodePostal(null);
            }
        }

        return $this;
    }
}
