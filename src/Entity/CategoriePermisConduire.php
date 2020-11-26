<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoriePermisConduireRepository")
 * 
 * @UniqueEntity("nomCategoriePermisConduire")
 */
class CategoriePermisConduire
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
     * @Assert\Length(min = 2, max = 5)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $nomCategoriePermisConduire;

    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @Assert\Length(min = 5, max = 150)
     */
    private $infoCategoriePermisConduire;

    /** 
     * @ORM\ManyToMany(targetEntity="App\Entity\PermisConduire", inversedBy="categoriePermisConduires")
     * 
     */
    private $permisConduires;

    public function __construct()
    {
        $this->permisConduires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCategoriePermisConduire(): ?string
    {
        return $this->nomCategoriePermisConduire;
    }

    public function setNomCategoriePermisConduire(string $nomCategoriePermisConduire): self
    {
        $this->nomCategoriePermisConduire = $nomCategoriePermisConduire;

        return $this;
    }

    public function getInfoCategoriePermisConduire(): ?string
    {
        return $this->infoCategoriePermisConduire;
    }

    public function setInfoCategoriePermisConduire(?string $infoCategoriePermisConduire): self
    {
        $this->infoCategoriePermisConduire = $infoCategoriePermisConduire;

        return $this;
    }

    /**
     * @return Collection|PermisConduire[]
     */
    public function getPermisConduires(): Collection
    {
        return $this->permisConduires;
    }

    public function addPermisConduire(PermisConduire $permisConduire): self
    {
        if (!$this->permisConduires->contains($permisConduire)) {
            $this->permisConduires[] = $permisConduire;
        }

        return $this;
    }

    public function removePermisConduire(PermisConduire $permisConduire): self
    {
        if ($this->permisConduires->contains($permisConduire)) {
            $this->permisConduires->removeElement($permisConduire);
        }

        return $this;
    }
}
