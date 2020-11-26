<?php

namespace App\Service\Logistique;

use DateTime;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface; 

class LogistiqueVehiculeService 
{
    private $manager;
    private $repoVehicule;

    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager, VehiculeRepository $repoVehicule)
    {
        $this->manager = $manager;
        $this->repoVehicule = $repoVehicule;
    }
    
    /**
     * Retourne la liste des objet "Chauffeur" sans affectation le jour en cours et actif
     * 
     * @return Vehicule[]
     */
    
    public function getVehiculeLibre(): array
    {
        $dateToday = new DateTime('today');
        $tableVehiculeLibre = array();
        $vehicules = $this->repoVehicule->findall();

        foreach ($vehicules as $vehicule)
        {
            $libre = true;
            $affectations = $vehicule->getAffectations();
            foreach ($affectations as $affectation)
            {
                if ($affectation->getDateAffectation() == $dateToday)
                {
                    $libre = false;
                }
            }

            if ($libre && $vehicule->getStatutVehicule()) $tableVehiculeLibre [] = clone $vehicule;
        }

        return $tableVehiculeLibre;
    }
    
    /**
     * Retourne la catégorie de permis en fonction de la MMA reçue en paramètre
     *
     * @param string $mma
     * 
     * @return array
     */
    public function getCategoriePermis($mma): array
    {   
        $categorie = array();

        if ($mma <= 3500 ) $categorie = ['B', 'BE', 'B96', 'C', 'C1', 'C1E', 'CE'];
        else if ($mma > 3500 && $mma < 7500  ) $categorie = [ 'C1', 'C1E','C', 'CE'];
        else if ($mma >= 7500 )  $categorie = ['C', 'CE'];

        return $categorie;
    }
}