<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ModeleVehiculeRepository")
 */
class ModeleVehicule
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
    private $nomModeleVehicule;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $marqueModeleVehicule;

    /**
     * @ORM\Column(type="integer")
     */
    private $capaciteModeleVehicule;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Vehicule", mappedBy="modeleVehicule")
     */
    private $vehicules;

    /**
     * @ORM\Column(type="integer")
     */
    private $intervalleEntretienModeleVehicule;

    public function __construct()
    {
        $this->vehicules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomModeleVehicule(): ?string
    {
        return $this->nomModeleVehicule;
    }

    public function setNomModeleVehicule(string $nomModeleVehicule): self
    {
        $this->nomModeleVehicule = $nomModeleVehicule;

        return $this;
    }

    public function getMarqueModeleVehicule(): ?string
    {
        return $this->marqueModeleVehicule;
    }

    public function setMarqueModeleVehicule(string $marqueModeleVehicule): self
    {
        $this->marqueModeleVehicule = $marqueModeleVehicule;

        return $this;
    }

    public function getCapaciteModeleVehicule(): ?int
    {
        return $this->capaciteModeleVehicule;
    }

    public function setCapaciteModeleVehicule(int $capaciteModeleVehicule): self
    {
        $this->capaciteModeleVehicule = $capaciteModeleVehicule;

        return $this;
    }

    public function getIntervalleEntretienModeleVehicule(): ?int
    {
        return $this->intervalleEntretienModeleVehicule;
    }

    public function setIntervalleEntretienModeleVehicule(int $intervalleEntretienModeleVehicule): self
    {
        $this->intervalleEntretienModeleVehicule = $intervalleEntretienModeleVehicule;

        return $this;
    }

    /**
     * @return Collection|Vehicule[]
     */
    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }

    public function addVehicule(Vehicule $vehicule): self
    {
        if (!$this->vehicules->contains($vehicule)) {
            $this->vehicules[] = $vehicule;
            $vehicule->setModeleVehicule($this);
        }

        return $this;
    }

    public function removeVehicule(Vehicule $vehicule): self
    {
        if ($this->vehicules->contains($vehicule)) {
            $this->vehicules->removeElement($vehicule);
            // set the owning side to null (unless already changed)
            if ($vehicule->getModeleVehicule() === $this) {
                $vehicule->setModeleVehicule(null);
            }
        }

        return $this;
    }
}
