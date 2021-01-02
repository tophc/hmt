<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SecretariatRepository")
 * 
 * @ORM\HasLifecycleCallbacks
 * 
 * @UniqueEntity("emailSecretariat")
 */
class Secretariat implements UserInterface
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
    private $nomSecretariat;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Length(min = 2, max = 100)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $prenomSecretariat;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Email
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $emailSecretariat;
 
    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\Type("string")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $passwordSecretariat;

    /**
     * @ORM\Column(type="json")
     */
    private $rolesSecretariat = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Requete", mappedBy="secretariat")
     * 
     * @Assert\Valid
     */
    private $requetes;

    public function __construct()
    {
        $this->requetes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSecretariat(): ?string
    {
        return $this->nomSecretariat;
    }

    public function setNomSecretariat(string $nomSecretariat): self
    {
        $this->nomSecretariat = $nomSecretariat;

        return $this;
    }

    public function getPrenomSecretariat(): ?string
    {
        return $this->prenomSecretariat;
    }

    public function setPrenomSecretariat(string $prenomSecretariat): self
    {
        $this->prenomSecretariat = $prenomSecretariat;

        return $this;
    }

    public function getEmailSecretariat(): ?string
    {
        return $this->emailSecretariat;
    }

    public function setEmailSecretariat(string $emailSecretariat): self
    {
        $this->emailSecretariat = $emailSecretariat;

        return $this;
    }

    public function getPasswordSecretariat(): ?string
    {
        return $this->passwordSecretariat;
    }

    public function setPassword(string $passwordSecretariat): self
    {
        $this->passwordSecretariat = $passwordSecretariat;

        return $this;
    }

    /**
     * @param array $roles
     * 
     * @return self
     */
    public Function setRoles(array $roles): self
    {
        $this->rolesSecretariat = $roles;
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
            $requete->setSecretariat($this);
        }

        return $this;
    }

    public function removeRequete(Requete $requete): self
    {
        if ($this->requetes->contains($requete)) {
            $this->requetes->removeElement($requete);
            // set the owning side to null (unless already changed)
            if ($requete->getSecretariat() === $this) {
                $requete->setSecretariat(null);
            }
        }

        return $this;
    }

    /* Fonctions implementées par l'interface UserInterface nécéssaire à l'authentification */

    public function getRoles()
    {
        $roles = $this->rolesSecretariat;
        return array_unique($roles);
    }
    
    // ****Fonction implementé par l'interface UserInterface*****//
    public function getPassword()
    {        
        return $this->passwordSecretariat;
    }
   
    // ****Fonction implementé par l'interface UserInterface*****//
	public function getSalt(){}
    
    // ****Fonction implementé par l'interface UserInterface*****//
    public function getUsername()
    {        
        return $this->emailSecretariat;  //dans notre entité l'email sert de login (username)          
    }

    // Fonction implementées par l'interface UserInterface
	public function eraseCredentials(){}

    /* Méthodes créées manuellement */

    /**
     * Permet d'initialiser le rôle d'un utilisateur nouvellement créé (uniquement à la création)
     *
     * @ORM\PrePersist
     * 
     * @return void
     */
    public function initializeRoles()
    {
        $this->rolesSecretariat = ['ROLE_NEW_USER','ROLE_SECRETARIAT']; 
    }
}
