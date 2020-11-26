<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LogistiqueRepository")
 * 
 * @ORM\HasLifecycleCallbacks
 * 
 * @UniqueEntity("emailLogistique")
 */
class Logistique implements UserInterface
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
    private $nomLogistique;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Length(min = 2, max = 100)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $prenomLogistique;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Email
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $emailLogistique;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Type("string")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $passwordLogistique;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Requete", mappedBy="logistique")
     * 
     * @Assert\Valid
     */
    private $requetes;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    public function __construct()
    {
        $this->requetes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomLogistique(): ?string
    {
        return $this->nomLogistique;
    }

    public function setNomLogistique(string $nomLogistique): self
    {
        $this->nomLogistique = $nomLogistique;

        return $this;
    }

    public function getPrenomLogistique(): ?string
    {
        return $this->prenomLogistique;
    }

    public function setPrenomLogistique(string $prenomLogistique): self
    {
        $this->prenomLogistique = $prenomLogistique;

        return $this;
    }

    public function getEmailLogistique(): ?string
    {
        return $this->emailLogistique;
    }

    public function setEmailLogistique(string $emailLogistique): self
    {
        $this->emailLogistique = $emailLogistique;

        return $this;
    }

    public function getPasswordLogistique(): ?string
    {
        return $this->passwordLogistique;
    }

    public function setPassword(string $passwordLogistique): self
    {
        $this->passwordLogistique = $passwordLogistique;

        return $this;
    }

    /**
     * @return Collection|Requete[]
     */
    public function getRequetes(): Collection
    {
        return $this->requetes;
    }

    public function addRequete(Requete $requete): self
    {
        if (!$this->requetes->contains($requete)) {
            $this->requetes[] = $requete;
            $requete->setLogistique($this);
        }

        return $this;
    }

    public function removeRequete(Requete $requete): self
    {
        if ($this->requetes->contains($requete)) {
            $this->requetes->removeElement($requete);
            // set the owning side to null (unless already changed)
            if ($requete->getLogistique() === $this) {
                $requete->setLogistique(null);
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
        $this->roles = $roles;
        
        return $this;
    }

    /* Fonctions implementées par l'interface UserInterface nécéssaire à l'authentification */
    /* A Modifier pour transformer le ArrayCollection d'objet retourné par getUserRole en tableau simple de chaine de caractère avec la fonction map */
    
    public function getRoles()
    {
        $roles = $this->roles;
        return array_unique($roles);
    }
    
    // ****Fonction implementé par l'interface UserInterface*****//
    public function getPassword()
    {        
        return $this->passwordLogistique;
    }
   
    // ****Fonction implementé par l'interface UserInterface*****//
	public function getSalt(){}
    
    // ****Fonction implementé par l'interface UserInterface*****//
    public function getUsername()
    {        
        return $this->emailLogistique;  //dans notre entité l'email sert de login (username)          
    }

    // Fonction implementées par l'interface UserInterface
	public function eraseCredentials(){}

    /* Méthodes créées manuellement */

    /**
     * Permet d'initialiser le rôle d'un utilisateur nouvellement créé 
     *
     * @ORM\PrePersist
     * 
     * @return void
     */
    public function initializeRoles()
    {
        if(empty($this->role))
        {
            $this->roles = ['ROLE_NEW_USER','ROLE_LOGISTIQUE'];
        }
    }
}
