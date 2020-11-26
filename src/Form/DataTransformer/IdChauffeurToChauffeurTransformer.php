<?php

namespace App\Form\DataTransformer;

use App\Entity\Chauffeur;
use App\Repository\ChauffeurRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

//Permet de transformer une donnée d'un champ de formulaire 
class IdChauffeurToChauffeurTransformer implements DataTransformerInterface  
{

    private $repoChauffeur;

    public function __construct(ChauffeurRepository $repoChauffeur)
    {
        $this->repoChauffeur = $repoChauffeur;
    }

    public function transform($objChauffeur) //reçoit un objet chauffeur et retourne la valeur de l'idChauffeur
    {
        if ($objChauffeur === null) {
            return '';
        }

        if (!$objChauffeur instanceof Chauffeur) {
            throw new \LogicException('The ChauffeurSelectTextType can only be used with Chauffeur objects');
        }
        return $objChauffeur->getId();
    }

    public function reverseTransform($idChauffeur) //reçoit une idChauffeur et retourne un objet Chauffeur
    {
       
        if ($idChauffeur === null) {
            throw new TransformationFailedException(sprintf('No driver found with id "%s"', $idChauffeur));
        }
        
        $chauffeur = new Chauffeur;
        $chauffeur = $this->repoChauffeur->find($idChauffeur);

        if ($chauffeur === null) {
            throw new TransformationFailedException(sprintf('No driver found with id "%s"', $idChauffeur));
        }
        
        return $chauffeur;
    }
}