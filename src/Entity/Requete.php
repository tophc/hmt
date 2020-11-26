<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RequeteRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Requete
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
     * @Assert\Type("string")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $objetRequete; 

    /**
     * @ORM\Column(type="text")
     * 
     * @Assert\Type("string")
     * @Assert\Length(min = 5, max = 250)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $messageRequete;

    /**
     * @ORM\Column(type="datetime")
     * 
     * @Assert\Valid
     */
    private $dateRequete;

    /**
     * @ORM\Column(type="boolean")
     * 
     * @Assert\Type("bool")
     */ 
    private $statutRequete;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @Assert\Valid
     */
    private $dateStatutRequete;

    /**
     * Le service de destination de la requête
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $serviceRequete;
    
    /**
     * Le servicedu requérant (chauffeur, secretariat, logistique)
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $requerantRequete;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fichierUrlRequete;

    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @Assert\Type("string")
     * @Assert\Length(min = 5, max = 250)
     *
     */
    private $noteRequete;

    /**
     * L'id du requérant si c'est un objet "Chauffeur"
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\Chauffeur", inversedBy="requetes")
     */
    private $chauffeur;

    /**
     * L'id du requérant si c'est un objet "Secretariat"
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\Secretariat", inversedBy="requetes")
     */
    private $secretariat;

    /**
     * L'id du requérant si c'est un objet "Logistique"
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\Logistique", inversedBy="requetes")
     */
    private $logistique;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjetRequete(): ?string
    {
        return $this->objetRequete;
    }

    public function setObjetRequete(string $objetRequete): self
    {
        $this->objetRequete = $objetRequete;

        return $this;
    }
    public function getMessageRequete(): ?string
    {
        return $this->messageRequete;
    }

    public function setMessageRequete(string $messageRequete): self
    {
        $this->messageRequete = $messageRequete;

        return $this;
    }

    public function getDateRequete(): ?\DateTimeInterface
    {
        return $this->dateRequete;
    }

    public function setDateRequete(\DateTimeInterface $dateRequete): self
    {
        $this->dateRequete = $dateRequete;

        return $this;
    }

    public function getStatutRequete(): ?bool
    {
        return $this->statutRequete;
    }

    public function setStatutRequete(bool $statutRequete): self
    {
        $this->statutRequete = $statutRequete;

        return $this;
    }

    public function getDateStatutRequete(): ?\DateTimeInterface
    {
        return $this->dateStatutRequete;
    }

    public function setDateStatutRequete(?\DateTimeInterface $dateStatutRequete): self
    {
        $this->dateStatutRequete = $dateStatutRequete;

        return $this;
    }

    public function getServiceRequete(): ?string
    {
        return $this->serviceRequete;
    }

    public function setServiceRequete(string $serviceRequete): self
    {
        $this->serviceRequete = $serviceRequete;

        return $this;
    }

    public function getRequerantRequete(): ?string
    {
        return $this->requerantRequete;
    }

    public function setRequerantRequete(string $requerantRequete): self
    {
        $this->requerantRequete = $requerantRequete;

        return $this;
    }

    public function getFichierUrlRequete(): ?string
    {
        return $this->fichierUrlRequete;
    }

    public function setFichierUrlRequete(?string $fichierUrlRequete): self
    {
        $this->fichierUrlRequete = $fichierUrlRequete;

        return $this;
    }

    public function getNoteRequete(): ?string
    {
        return $this->noteRequete;
    }

    public function setNoteRequete(?string $noteRequete): self
    {
        $this->noteRequete = $noteRequete;

        return $this;
    }

    public function getChauffeur(): ?Chauffeur
    {
        return $this->chauffeur;
    }

    public function setChauffeur(?Chauffeur $chauffeur): self
    {
        $this->chauffeur = $chauffeur;

        return $this;
    }

    public function getSecretariat(): ?Secretariat
    {
        return $this->secretariat;
    }

    public function setSecretariat(?Secretariat $secretariat): self
    {
        $this->secretariat = $secretariat;

        return $this;
    }

    public function getLogistique(): ?Logistique
    {
        return $this->logistique;
    }

    public function setLogistique(?Logistique $logistique): self
    {
        $this->logistique = $logistique;

        return $this;
    }

     /****Méthodes créées manuellement****/

     /**
     * Permet d'initialiser le statutRequete avant la persisance en DB (création)
     * 
     * @ORM\PrePersist
     *
     * @return void
     */
    public function initializeStatutRequete() {
         if($this->statutRequete === null){
            $this->statutRequete = 1;
        }
    }
    /**
     * Permet d'initialiser le dateRequete avant la persisance en DB (création)
     * 
     * @ORM\PrePersist
     *
     * @return void
     */
    public function initializeDateRequete() {
        if(empty($this->dateRequete)){
            $this->dateRequete = new \DateTime();
        }
    }

    /**
     * Permet d'initialiser le dateSatutRequete avant la persisance en DB (création)
     *  
     * @ORM\PrePersist
     *
     * @return void
     */
    public function initializeDateSatutRequete() {
        if(empty($this->dateSatutRequete)){
            $this->dateSatutRequete = null;
        }
    }
}
