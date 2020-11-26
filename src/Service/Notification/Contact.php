<?php 
 
namespace App\Service\Notification;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    /**
     * @var string|null
     * 
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=100)
     * 
     */
    private $nom;

    /**
     * @var string|null
     * 
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=100)
     * 
     */
    private $prenom;
    
    /**
     * @var string|null
     * 
     * @Assert\NotBlank()
     * @Assert\Email()
     * 
     */
    private $email;

    /**
     * @var string|null
     * 
     * @Assert\NotBlank()
     * 
     */
    private $service;

    /**
     * @var string|null
     * 
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=100)
     */
    private $sujet;
    
    /**
     * @var string|null
     * 
     * @Assert\NotBlank()
     * @Assert\Length(min=10, max=250)
     * 
     */
    private $message;

    /**
     * @var file|blank
     * 
     * @Assert\File(
     *     maxSize = "4096k",
     *     maxSizeMessage = "Message from assert contact.php : The uploaded file was too large.For Contact Please try to upload a smaller fil.",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "Message from assert contact.php : Please upload a valid PDF",
     *     uploadIniSizeErrorMessage = "Message from assert contact.php : Internal server error : Max file size is 4096 Ko"
     * )
     */
    private $fichier;

    /**
     * @var string|null
     */
    private $nomFichier;

    /**
     * Get the value of nom
     *
     * @return  string|null
     */ 
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set the value of nom 
     *
     * @param  string|null  $nom
     *
     * @return  self
     */ 
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get the value of prenom
     *
     * @return  string|null
     */ 
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set the value of prenom
     *
     * @param  string|null  $prenom
     *
     * @return  self
     */ 
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get the value of telephonneMobile
     *
     * @return  string|null
     */ 
    public function getTelephonneMobile()
    {
        return $this->telephonneMobile;
    }

    /**
     * Set the value of telephonneMobile
     *
     * @param  string|null  $telephonneMobile
     *
     * @return  self
     */ 
    public function setTelephonneMobile($telephonneMobile)
    {
        $this->telephonneMobile = $telephonneMobile;

        return $this;
    }

    /**
     * Get the value of telephonneFixe
     *
     * @return  string|null
     */ 
    public function getTelephonneFixe()
    {
        return $this->telephonneFixe;
    }

    /**
     * Set the value of telephonneFixe
     *
     * @param  string|null  $telephonneFixe
     *
     * @return  self
     */ 
    public function setTelephonneFixe($telephonneFixe)
    {
        $this->telephonneFixe = $telephonneFixe;

        return $this;
    }

    /**
     * Get the value of email
     *
     * @return  string|null
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param  string|null  $email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of service
     *
     * @return  string|null
     */ 
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set the value of service
     *
     * @param  string|null  $service
     *
     * @return  self
     */ 
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get the value of message
     *
     * @return  string|null
     */ 
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set the value of message
     *
     * @param  string|null  $message
     *
     * @return  self
     */ 
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the value of sujet
     *
     * @return  string|null
     */ 
    public function getSujet()
    {
        return $this->sujet;
    }

    /**
     * Set the value of sujet
     *
     * @param  string|null  $sujet
     *
     * @return  self
     */ 
    public function setSujet($sujet)
    {
        $this->sujet = $sujet;

        return $this;
    }

    /**
     * Get the value of nomFichier
     *
     * @return  string|null
     */ 
    public function getNomFichier()
    {
        return $this->nomFichier;
    }

    /**
     * Set the value of nomFichier
     *
     * @param  string|null  $nomFichier
     *
     * @return  self
     */ 
    public function setNomFichier($nomFichier)
    {
        $this->nomFichier = $nomFichier;

        return $this;
    }

    public function setFichier(File $file = null)
    {
        $this->fichier = $file;
    }

    public function getFichier()
    {
        return $this->fichier;
    }
}