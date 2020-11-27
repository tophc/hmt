<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\VehiculeRepository")
 * 
 * @ORM\HasLifecycleCallbacks
 * 
 * @UniqueEntity("immatriculationVehicule")
 * @UniqueEntity("numChassisVehicule")
 */
class Vehicule
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
     * @Assert\Type(type={"alnum"})
     * @Assert\NotNull
     * @Assert\NotBlank
     * 
     */
    private $immatriculationVehicule;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Type(type={"alnum"})
     * @Assert\NotNull
     * @Assert\NotBlank
     * 
     */
    private $numChassisVehicule;

    /**
     * @ORM\Column(type="boolean")
     * 
     * @Assert\Type("bool")
     */
    private $statutVehicule;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ModeleVehicule", inversedBy="vehicules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $modeleVehicule;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Amende", mappedBy="vehicule")
     */
    private $amendes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Affectation", mappedBy="vehicule")
     */
    private $affectations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Entretien", mappedBy="vehicule", orphanRemoval=true)
     * @ORM\OrderBy({"dateEntretien" = "DESC"})
     */
    private $entretiens;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ControleTechnique", mappedBy="vehicule", orphanRemoval=true)
     * @ORM\OrderBy({"dateControleTechnique" = "DESC"})
     */
    private $controleTechniques;
  
    public function __construct()
    {
        $this->amendes = new ArrayCollection();
        $this->affectations = new ArrayCollection();
        $this->entretiens = new ArrayCollection();
        $this->controleTechniques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImmatriculationVehicule(): ?string
    {
        return $this->immatriculationVehicule;
    }

    public function setImmatriculationVehicule(string $immatriculationVehicule): self
    {
        $this->immatriculationVehicule = strtoupper($immatriculationVehicule);

        return $this;
    }
    
    public function getNumChassisVehicule(): ?string
    {
        return $this->numChassisVehicule;
    }

    public function setNumChassisVehicule(string $numChassisVehicule): self
    {
        $this->numChassisVehicule = strtoupper($numChassisVehicule);

        return $this;
    }

    public function getStatutVehicule(): ?bool
    {
        return $this->statutVehicule;
    }

    public function setStatutVehicule(?bool $statutVehicule): self
    {
        $this->statutVehicule = $statutVehicule;

        return $this;
    }

    public function getModeleVehicule(): ?ModeleVehicule
    {
        return $this->modeleVehicule;
    }

    public function setModeleVehicule(?ModeleVehicule $modeleVehicule): self
    {
        $this->modeleVehicule = $modeleVehicule;

        return $this;
    }

    /**
     * @return Collection|Amende[]
     */
    public function getAmendes(): Collection
    {
        return $this->amendes;
    }

    public function addAmende(Amende $amende): self
    {
        if (!$this->amendes->contains($amende)) {
            $this->amendes[] = $amende;
            $amende->setVehicule($this);
        }

        return $this;
    }

    public function removeAmende(Amende $amende): self
    {
        if ($this->amendes->contains($amende)) {
            $this->amendes->removeElement($amende);
            // set the owning side to null (unless already changed)
            if ($amende->getVehicule() === $this) {
                $amende->setVehicule(null);
            }
        }

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
            $affectation->setVehicule($this);
        }

        return $this;
    }

    public function removeAffectation(Affectation $affectation): self
    {
        if ($this->affectations->contains($affectation)) {
            $this->affectations->removeElement($affectation);
            // set the owning side to null (unless already changed)
            if ($affectation->getVehicule() === $this) {
                $affectation->setVehicule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Entretien[]
     */
    public function getEntretiens(): Collection
    {
        return $this->entretiens;
    }

    public function addEntretien(Entretien $entretien): self
    {
        if (!$this->entretiens->contains($entretien)) {
            $this->entretiens[] = $entretien;
            $entretien->setVehicule($this);
        }

        return $this;
    }

    public function removeEntretien(Entretien $entretien): self
    {
        if ($this->entretiens->contains($entretien)) {
            $this->entretiens->removeElement($entretien);
            // set the owning side to null (unless already changed)
            if ($entretien->getVehicule() === $this) {
                $entretien->setVehicule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ControleTechnique[]
     */
    public function getControleTechniques(): Collection
    {

        return $this->controleTechniques;
    }

    public function addControleTechnique(ControleTechnique $controleTechnique): self
    {
        if (!$this->controleTechniques->contains($controleTechnique)) {
            $this->controleTechniques[] = $controleTechnique;
            $controleTechnique->setVehicule($this);
        }

        return $this;
    }

    public function removeControleTechnique(ControleTechnique $controleTechnique): self
    {
        if ($this->controleTechniques->contains($controleTechnique)) {
            $this->controleTechniques->removeElement($controleTechnique);
            // set the owning side to null (unless already changed)
            if ($controleTechnique->getVehicule() === $this) {
                $controleTechnique->setVehicule(null);
            }
        }

        return $this;
    }

    /* Méthodes créées manuellement */
    /**
     * Permet d'initialiser le statut vehicule avant la persisance en DB (création et mise à jour)
     * 
     *@ORM\PrePersist
     *
     * @return void
     */
    public function initializeStatutVehicule() {
        if(empty($this->statutVehicule)){
            $this->statutVehicule = true;
        }
    }

}
