<?php

namespace App\Service\Logistique;

use DateTime;
use DateInterval;
use App\Repository\VehiculeRepository;
use App\Repository\ChauffeurRepository;
use Doctrine\ORM\EntityManagerInterface; 

class LogistiqueChauffeurService 
{
    private $manager;
    private $repoChauffeur;
    private $repoVehicule;
    private $logistiqueVehiculeService;

    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     * @param ChauffeurRepository $repoChauffeur
     * @param VehiculeRepository $repoVehicule
     * @param LogistiqueVehiculeService $logistiqueVehiculeService
     */
    public function __construct(EntityManagerInterface $manager, ChauffeurRepository $repoChauffeur, VehiculeRepository $repoVehicule, LogistiqueVehiculeService $logistiqueVehiculeService)
    {
        $this->manager                      = $manager;
        $this->repoChauffeur                = $repoChauffeur;
        $this->repoVehicule                 = $repoVehicule;
        $this->logistiqueVehiculeService    = $logistiqueVehiculeService;
    }

    /**
     * Retourne la liste des objet "Chauffeur" sans affectations le jour en cours | un permis valide | un statut actif
     *
     * @return array
     */
    public function getChauffeurLibre(): array
    {
        $dateToday = new DateTime('today');
        $tableChauffeurLibre = array();
        $chauffeurs = $this->repoChauffeur->findall();

        foreach ($chauffeurs as $chauffeur)
        {
            $libre = true;
            $affectations = $chauffeur->getAffectations();
            foreach ($affectations as $affectation)
            {
                if ($affectation->getDateAffectation() == $dateToday)
                {
                    $libre = false;
                }
            }

            if ($libre && $chauffeur->getStatutChauffeur() &&  $chauffeur->getPermisConduire() != null && $chauffeur->getPermisConduire()->getDateValPermisConduire() > date('Y-m-d')) $tableChauffeurLibre [] = clone $chauffeur;
        }

        return $tableChauffeurLibre;
    }

    /**
     * Retourne la "mma" selon la catégorie de permis la plus élevée
     *
     * @param array $categories
     * 
     * @return int
     */
    public function getMma($categories): int
    {
        $nomCategorie = array();

        if ($categories === null) 
        {
            return  0;
        }
        else
        {
            foreach ($categories as $categorie)
            {
              $nomCategorie [] = $categorie->getNomCategoriePermisConduire();   
            }
            // Par ordre de "grandeur" : on récupère la "mma" de la plus "grosse" catégorie
            if (in_array( 'CE' , $nomCategorie )) return 40000;
            if (in_array( 'C' , $nomCategorie )) return 40000;
            if (in_array( 'C1E' , $nomCategorie )) return 7500;
            if (in_array( 'C1' , $nomCategorie )) return 7500;
            if (in_array( 'B96' , $nomCategorie )) return 3500;
            if (in_array( 'BE' , $nomCategorie )) return 3500; 
            if (in_array( 'B' , $nomCategorie )) return 3500;    
        }
        
        // Vérifier la pertinence : 0, false ou une valeur négative
        return 0;
    }

    /**
     * Retourne un tableau d'objets "Chauffeur" ayant le permis adéquat pour l'objet "Vehicule" passé en paramètre
     *
     * @param Vehicule $idVehicule
     * 
     * @return array
     */
    public function getChauffeurByCategoriePermisConduire($idVehicule): array
    {
        $vehicule = $this->repoVehicule->find($idVehicule);
        $mma = $vehicule->getModeleVehicule()->getCapaciteModeleVehicule();
        $categoriePermis = $this->logistiqueVehiculeService->getCategoriePermis($mma);

        $chauffeurs = $this->repoChauffeur->getChauffeurByCategoriePermis($categoriePermis);

        return $chauffeurs ;  
    }
}