<?php

namespace App\Service\Upload;

use DateTimeInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class Fichier 
{
    /**
     * @var file|blank
     * 
     * @Assert\File(
     *     maxSize = "4096k",
     *     maxSizeMessage = "Message from assert fichier.php : The file was too large. Please try to upload a smaller file.",
     *     mimeTypes = {"text/plain", "text/csv" },
     *     mimeTypesMessage = "Message from assert fichier.php : Please upload a valid CSV.",
     *     uploadIniSizeErrorMessage = "Message from assert fichier.php : Internal server error : Max file size is 4096 Ko" 
     * )
     * 
     */
    private $fichier;

    /**
     * @var string|null
     */
    private $nomFichier;

    /**
     * @var DateTimeInterface|null
     */
    private $dateFichier;

    public function setFichier(File $file = null)
    {
        $this->fichier = $file;
    }

    public function getFichier()
    {
        return $this->fichier;
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

    /**
     * Get the value of nomFichier
     *
     * @return  string|null
     */ 
    public function getNomFichier()
    {
        return $this->nomFichier;
    }

    public function getdateFichier(): ?\DateTimeInterface
    {
        return $this->dateFichier;
    }

    public function setdateFichier(\DateTimeInterface $dateFichier): self
    {
        $this->dateFichier = $dateFichier;

        return $this;
    }
}