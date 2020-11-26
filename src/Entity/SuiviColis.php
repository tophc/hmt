<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SuiviColisRepository")
 */
class SuiviColis
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
    private $dateSuiviColis;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Colis", inversedBy="suiviColis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $colis;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etat", inversedBy="suiviColis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateSuiviColis(): ?\DateTimeInterface
    {
        return $this->dateSuiviColis;
    }

    public function setDateSuiviColis(\DateTimeInterface $dateSuiviColis): self
    {
        $this->dateSuiviColis = $dateSuiviColis;

        return $this;
    }

    public function getColis(): ?Colis
    {
        return $this->colis;
    }

    public function setColis(?Colis $colis): self
    {
        $this->colis = $colis;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
}
