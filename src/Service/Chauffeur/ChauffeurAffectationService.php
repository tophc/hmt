<?php

namespace App\Service\Chauffeur;

use DateInterval;
use App\Repository\AmendeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AffectationRepository;

class ChauffeurAffectationService 
{
    private $manager;
    private $repoAmende;
    private $repoAffectation;
    
    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     * @param AmendeRepository $repoAmende
     * @param AffectationRepository $repoAffectation
     */
    public function __construct(EntityManagerInterface $manager, AmendeRepository $repoAmende, AffectationRepository $repoAffectation)
    {
        $this->manager          = $manager;   
        $this->repoAmende       = $repoAmende;
        $this->repoAffectation  = $repoAffectation;
    }
    
     /**
     * Renvoi un tableau d'objets "Amende" éventuelement filtré par mois
     *
     * @param Chauffeur $chauffeur
     * @param Date $date|null
     * 
     * @return array
     */
    public function getAmendeByChauffeur($chauffeur, $date = null): array
    {   
        $tableauAmendes = array();

        if ($date)
        {
            list($year,$month) = explode("/",  $date->format('Y/m'));
            $amendes = $this->repoAmende->getAmendeMois($month, $year);

            foreach ($amendes as $amende)
            {
                $vehicule = $amende->getVehicule()->getId();
                $date = $amende->getDateAmende();
                $affectationAmende = $this->repoAffectation->FindBy(['dateAffectation' => $date ,'vehicule' => $vehicule, "chauffeur" => $chauffeur ]);

                if ($affectationAmende != null) $tableauAmendes [] =  $amende;
            }   
        }
        else
        {
            $amendes = $this->repoAmende->findBy([], ['dateAmende' => 'DESC']);
            
            foreach ($amendes as $amende)
            {
                $affectationAmende = $this->repoAffectation->findOneBy(['dateAffectation' => $amende->getDateAmende(), 'vehicule' => $amende->getVehicule(), 'chauffeur' => $chauffeur ], []);
    
                if ($affectationAmende != null)
                {
                    $tableauAmendes [] =  $amende;
                }
            } 
        }
        
        return $tableauAmendes;
    }
}    