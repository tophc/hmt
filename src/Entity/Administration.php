<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdministrationRepository")
 */
class Administration implements UserInterface
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
    private $nomAdministration;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenomAdministration;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $emailAdministration;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $passwordAdministration;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomAdministration(): ?string
    {
        return $this->nomAdministration;
    }

    public function setNomAdministration(string $nomAdministration): self
    {
        $this->nomAdministration = $nomAdministration;

        return $this;
    }

    public function getPrenomAdministration(): ?string
    {
        return $this->prenomAdministration;
    }

    public function setPrenomAdministration(string $prenomAdministration): self
    {
        $this->prenomAdministration = $prenomAdministration;

        return $this;
    }

    public function getEmailAdministration(): ?string
    {
        return $this->emailAdministration;
    }

    public function setEmailAdministration(string $emailAdministration): self
    {
        $this->emailAdministration = $emailAdministration;

        return $this;
    }

    public function getPasswordAdministration(): ?string
    {
        return $this->passwordAdministration;
    }

    public function setPassword(string $passwordAdministration): self
    {
        $this->passwordAdministration = $passwordAdministration;

        return $this;
    }

    // ****Fonction implementé par l'interface UserInterface nécéssaire à l'authentification*****//
    //**** A Modifier pour transformer le ArrayCollection d'objet retourné par getUserRole en tableau simple de chaine de caractère avec la fonction map*/
    public function getRoles()
    {
		return ['ROLE_ADMINISTRATION'];
    }
    
    // ****Fonction implementé par l'interface UserInterface*****//
    public function getPassword()
    {        
        return $this->passwordAdministration;
    }
   
    // ****Fonction implementé par l'interface UserInterface*****//
	public function getSalt(){}
    
    // ****Fonction implementé par l'interface UserInterface*****//
    public function getUsername()
    {        
        return $this->emailAdministration;  //dans notre entité l'email sert de login (username)          
    }

    // Fonction implementées par l'interface UserInterface
	public function eraseCredentials(){}
}
