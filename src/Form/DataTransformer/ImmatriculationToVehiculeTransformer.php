<?php

namespace App\Form\DataTransformer;

use App\Entity\Vehicule;
use App\Repository\VehiculeRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

//Permet de transformer une donnée d'un champ de formulaire 
class ImmatriculationToVehiculeTransformer implements DataTransformerInterface  
{

    private $vehiculeRepository;

    public function __construct(VehiculeRepository $vehiculeRepository)
    {
        $this->vehiculeRepository = $vehiculeRepository;
    }

    public function transform($objVehicule) //reçoit un objet vehicule et retourne la valeur de l'immatriculation
    {
        if ($objVehicule === null) {
            return '';
        }

        if (!$objVehicule instanceof Vehicule) {
            throw new \LogicException('The ImmatriculationSelectTextType can only be used with Vehicule objects');
        }
        return $objVehicule->getImmatriculationVehicule();
    }

    public function reverseTransform($stringImmatriculation) //reçoit une immatriculation et retourne un objet vehicule
    {
       $vehicule = $this->vehiculeRepository->findOneBy(['immatriculationVehicule' => $stringImmatriculation]);
        if (!$vehicule) {
            throw new TransformationFailedException(sprintf('No Vehicule found with numberplate "%s"', $stringImmatriculation));
        }
        return $vehicule;
    }
}