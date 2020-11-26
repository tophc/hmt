<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PermisConduireRepository")
 * 
 * @UniqueEntity("numPermisConduire")
 */
class PermisConduire
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
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $numPermisConduire;


    /**
     * @ORM\Column(type="date")
     * 
     * @Assert\Type("object")
     * @Assert\NotNull
     * @Assert\NotBlank
     */ 
    private $dateValPermisConduire;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Chauffeur", inversedBy="permisConduire")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\Type("object")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $titulairePermisConduire;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CategoriePermisConduire", mappedBy="permisConduires")
     * 
     * @Assert\Valid
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $categoriePermisConduires;

    public function __construct()
    {
        $this->categoriePermisConduires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumPermisConduire(): ?string
    {
        return $this->numPermisConduire;
    }

    public function setNumPermisConduire(string $numPermisConduire): self
    {
        $this->numPermisConduire = $numPermisConduire;

        return $this;
    }

    public function getDateValPermisConduire(): ?\DateTimeInterface
    {
        return $this->dateValPermisConduire;
    }

    public function setDateValPermisConduire(\DateTimeInterface $dateValPermisConduire): self
    {
        $this->dateValPermisConduire = $dateValPermisConduire;

        return $this;
    }

    public function getTitulairePermisConduire(): ?Chauffeur
    {
        return $this->titulairePermisConduire;
    }

    public function setTitulairePermisConduire(?Chauffeur $titulairePermisConduire): self
    {
        $this->titulairePermisConduire = $titulairePermisConduire;

        return $this;
    }

    /**
     * @return Collection|CategoriePermisConduire[]
     */
    public function getCategoriePermisConduires(): Collection
    {
        return $this->categoriePermisConduires;
    }

    public function addCategoriePermisConduire(CategoriePermisConduire $categoriePermisConduire): self
    {
        if (!$this->categoriePermisConduires->contains($categoriePermisConduire)) {
            $this->categoriePermisConduires[] = $categoriePermisConduire;
            $categoriePermisConduire->addPermisConduire($this);
        }

        return $this;
    }

    public function removeCategoriePermisConduire(CategoriePermisConduire $categoriePermisConduire): self
    {
        if ($this->categoriePermisConduires->contains($categoriePermisConduire)) {
            $this->categoriePermisConduires->removeElement($categoriePermisConduire);
            $categoriePermisConduire->removePermisConduire($this);
        }

        return $this;
    }
}
