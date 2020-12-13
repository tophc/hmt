<?php

namespace App\Entity; 

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface; 

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChauffeurRepository")
 * 
 * @ORM\HasLifecycleCallbacks
 * 
 * @UniqueEntity("emailChauffeur")
 * 
 */
class Chauffeur implements UserInterface
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
     * @Assert\Length(min = 2, max = 100)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $nomChauffeur;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Length(min = 2, max = 100)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $prenomChauffeur;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Type(type={"alpha"})
     * @Assert\Length(min = 2, max = 20)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $genreChauffeur;

    /**
     * @ORM\Column(type="string")
     * 
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\Type(type={"digit"})
     * @Assert\Length(min = 11, max = 11)
     */
    private $numeroNationalChauffeur;

    /**
     * @ORM\Column(type="date")
     * 
     * @Assert\Type("object")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $dateNaissanceChauffeur;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Type("string")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $adressePostaleChauffeur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @Assert\Type("numeric")
     * @Assert\Length(min = 2, max = 20)
     */
    private $mobileChauffeur;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Email
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $emailChauffeur;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Type("string")
     * @Assert\NotNull
     * @Assert\NotBlank
     * 
     */
    private $passwordChauffeur;
 
    /**
     * @ORM\Column(type="boolean", nullable=true)
     * 
     * @Assert\Type("bool")
     */
    private $statutChauffeur;

    /**
     * @ORM\Column(type="json")
     * 
     * @Assert\Type("Array")
     */
    private $rolesChauffeur = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EtatCivil", inversedBy="chauffeurs")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\Valid
     */
    private $etatCivilChauffeur;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PermisConduire", mappedBy="titulairePermisConduire", cascade={"persist", "remove"})
     */
    private $permisConduire;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Affectation", mappedBy="chauffeur")
     * 
     * @Assert\Valid
     */
    private $affectations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Requete", mappedBy="chauffeur", orphanRemoval=true)
     * 
     * @Assert\Valid
     */
    private $requetes;
    
    public function __construct()
    {
        $this->affectations = new ArrayCollection();
        $this->requetes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomChauffeur(): ?string
    {
        return $this->nomChauffeur;
    }

    public function setNomChauffeur(string $nomChauffeur): self
    {
        $this->nomChauffeur = $nomChauffeur;

        return $this;
    }

    public function getPrenomChauffeur(): ?string
    {
        return $this->prenomChauffeur;
    }

    public function setPrenomChauffeur(string $prenomChauffeur): self
    {
        $this->prenomChauffeur = $prenomChauffeur;

        return $this;
    }

    public function getGenreChauffeur(): ?string
    {
        return $this->genreChauffeur;
    }

    public function setGenreChauffeur(string $genreChauffeur): self
    {
        $this->genreChauffeur = $genreChauffeur;

        return $this;
    }

    public function getNumeroNationalChauffeur(): ?string
    {
        return $this->numeroNationalChauffeur;
    }

    public function setNumeroNationalChauffeur(string $numeroNationalChauffeur): self
    {
        $this->numeroNationalChauffeur = $numeroNationalChauffeur;

        return $this;
    }

    public function getDateNaissanceChauffeur(): ?\DateTimeInterface
    {
        return $this->dateNaissanceChauffeur;
    }

    public function setDateNaissanceChauffeur(\DateTimeInterface $dateNaissanceChauffeur): self
    {
        $this->dateNaissanceChauffeur = $dateNaissanceChauffeur;

        return $this;
    }

    public function getAdressePostaleChauffeur(): ?string
    {
        return $this->adressePostaleChauffeur;
    }

    public function setAdressePostaleChauffeur(string $adressePostaleChauffeur): self
    {
        $this->adressePostaleChauffeur = $adressePostaleChauffeur;

        return $this;
    }

    public function getMobileChauffeur(): ?string
    {
        return $this->mobileChauffeur;
    }

    public function setMobileChauffeur(?string $mobileChauffeur): self
    {
        $this->mobileChauffeur = $mobileChauffeur;

        return $this;
    }

    public function getEmailChauffeur(): ?string
    {
        return $this->emailChauffeur;
    }

    public function setEmailChauffeur(string $emailChauffeur): self
    {
        $this->emailChauffeur = $emailChauffeur;

        return $this;
    }

    public function getPasswordChauffeur(): ?string
    {
        return $this->passwordChauffeur;
    }

    public function setPassword(string $passwordChauffeur): self
    {
        $this->passwordChauffeur = $passwordChauffeur;

        return $this;
    }

    public function getStatutChauffeur(): ?bool
    {
        return $this->statutChauffeur;
    }

    public function setStatutChauffeur(bool $statutChauffeur): self
    {
        $this->statutChauffeur = $statutChauffeur;

        return $this;
    }

    public function getEtatCivilChauffeur(): ?etatCivil
    {
        return $this->etatCivilChauffeur;
    }

    public function setEtatCivilChauffeur(?etatCivil $etatCivilChauffeur): self
    {
        $this->etatCivilChauffeur = $etatCivilChauffeur;

        return $this;
    }
	
    public function getPermisConduire(): ?PermisConduire
    {
        return $this->permisConduire;
    }

    /**
     * @param PermisConduire $permisConduire
     * @return self
     */
    public function setPermisConduire(PermisConduire $permisConduire): self
    {
        $this->permisConduire = $permisConduire;
        // set the owning side of the relation if necessary
        if ($permisConduire->getTitulairePermisConduire() !== $this) {
            $permisConduire->setTitulairePermisConduire($this);
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
            $affectation->setChauffeur($this);
        }

        return $this;
    }

    /**
     * @param Affectation $affectation
     * @return self
     */
    public function removeAffectation(Affectation $affectation): self
    {
        if ($this->affectations->contains($affectation)) {
            $this->affectations->removeElement($affectation);
            // set the owning side to null (unless already changed)
            if ($affectation->getChauffeur() === $this) {
                $affectation->setChauffeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Requete[]
     */
    public function getRequetes(): Collection
    {
        return $this->requetes;
    }

    /**
     * @param Requete $requete
     * @return self
     */
    public function addRequete(Requete $requete): self
    {
        if (!$this->requetes->contains($requete)) {
            $this->requetes[] = $requete;
            $requete->setChauffeur($this);
        }

        return $this;
    }

    public function removeRequete(Requete $requete): self
    {
        if ($this->requetes->contains($requete)) {
            $this->requetes->removeElement($requete);
            // set the owning side to null (unless already changed)
            if ($requete->getChauffeur() === $this) {
                $requete->setChauffeur(null);
            }
        }

        return $this;
    }

    /**
     * @param array $roles
     * 
     * @return self
     */
    public Function setRoles(array $roles): self
    {
        $this->rolesChauffeur = $roles;
        
        return $this;
    }

    // Fonctions implementées par l'interface UserInterface nécéssaire à l'authentification 
    // A Modifier pour transformer le ArrayCollection d'objet retourné par getUserRole en tableau simple de chaine de caractère avec la fonction map
    // Plus nécessaire avec le type json
    public function getRoles(): array
    {
        $roles = $this->rolesChauffeur;
        
        return array_unique($roles);
    }
    
    /* Fonction implementée par l'interface UserInterface */
    public function getPassword()
    {        
        return $this->passwordChauffeur;
    }
   
    /* Fonction implementée par l'interface UserInterface */
	public function getSalt(){}
    
    /* Fonction implementée par l'interface UserInterface */
    public function getUsername()
    {        
        return $this->emailChauffeur;  //dans notre entité l'email sert de login (username)          
    }

    // Fonction implementée par l'interface UserInterface
    public function eraseCredentials(){}
    
    /* Méthodes créées manuellement */

    /**
     * Permet d'initialiser le statut Chauffeur avant la persisance en DB (création et mise à jour)
     * 
     *@ORM\PrePersist
     *
     * @return void
     */
    public function initializeStatutChauffeur() {
        if(empty($this->statutChauffeur))
        {
            $this->statutChauffeur = true;
        }
    }

    /**
     * Permet d'initialiser le rôle d'un utilisateur nouvellement créé
     *
     * @ORM\PrePersist
     *
     * 
     * @return void
     */
    public function initializeRoles()
    {
        $this->rolesChauffeur =['ROLE_NEW_USER', 'ROLE_CHAUFFEUR'];
    }
}
