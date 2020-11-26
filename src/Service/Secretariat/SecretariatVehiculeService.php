<?php

namespace App\Service\Secretariat;

use DateTime;
use DateInterval;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecretariatVehiculeService
{
    private $manager;
    private $translator;
    private $repoVehicule;
    
    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     * @param VehiculeRepository $repoVehicule
     */
    public function __construct(EntityManagerInterface $manager, TranslatorInterface $translator, VehiculeRepository $repoVehicule)
    {
        $this->manager                  = $manager;
        $this->translator               = $translator;
        $this->repoVehicule             = $repoVehicule;
    }

    public function getVehiculeControleTechniqueExpire()
    {
        $tableauVehiculeControleTechniqueExpire = array();
        $vehicules = $this->repoVehicule->findBy(['statutVehicule' => '1']);
        $dernierCT = null;

        foreach($vehicules as $vehicule)
        {   
            $ct = array();
            $ct = clone $vehicule->getControleTechniques(); 
            $dernierCT = $ct[0];
            
            if ($dernierCT)
            {
                $date = $dernierCT->getDateControleTechnique();
                $resultat = intval(($date->diff(new DateTime()))->format('%R%a'));
                
                if ($resultat > 365 && $dernierCT->getStatutControleTechnique() === false) 
                {
                    $tableauVehiculeControleTechniqueExpire[] = $vehicule;
                }
            }
        }

        return $tableauVehiculeControleTechniqueExpire;
    }
    
    public function getVehiculeControleTechniqueBientot()
    {
        $tableauVehiculeControleTechniqueBientot = array();
        $vehicules = $this->repoVehicule->findBy(['statutVehicule' => '1']);
        $dernierCT = null;
        
        foreach($vehicules as $vehicule)
        {   
            $ct = array();
            $ct = clone $vehicule->getControleTechniques(); 
            $dernierCT = $ct[0];
            
            if ($dernierCT)
            {
                $date = $dernierCT->getDateControleTechnique();
                $resultat = intval(($date->diff(new DateTime()))->format('%R%a'));
                if ($resultat > 335 && $resultat < 365 && $dernierCT->getStatutControleTechnique() === false ) 
                {
                    $tableauVehiculeControleTechniqueBientot[] = $vehicule;
                }
            }
        }

        return $tableauVehiculeControleTechniqueBientot;
    }
   
    public function getVehiculeControleTechniqueRefuse()
    {
        $tableauVehiculeControleTechniqueExpire = array();
        $vehicules = $this->repoVehicule->findBy(['statutVehicule' => '1']);
        $dernierCT = null;

        foreach($vehicules as $vehicule)
        {   
            $ct = array();
            $ct = clone $vehicule->getControleTechniques(); 
            $dernierCT = $ct[0];
            
            if ($dernierCT)
            {
                if ($dernierCT->getStatutControleTechnique() === true) 
                {
                    $tableauVehiculeControleTechniqueExpire[] = $vehicule;
                }
            }
        }

        return $tableauVehiculeControleTechniqueExpire;
    }

    public function getVehiculeEntretien()
    {
        $tableauVehiculeEntretien = array();
        $vehicules = $this->repoVehicule->findBy(['statutVehicule' => '1']);
        $dernierEntretien = null;

        foreach($vehicules as $vehicule)
        {   
            $entretien = array();
            $entretien = clone $vehicule->getEntretiens(); 
            $dernierEntretien = $entretien[0];
            
            if ($dernierEntretien)
            {
                $date = $dernierEntretien->getDateEntretien();
                //$interval = $vehicule->getModeleVehicule()->getIntervalleEntretienModeleVehicule();
                $resultat = intval(($date->diff(new DateTime()))->format('%R%a'));
                // entretien une fois par an. A ajouter : en fontione du kilometrage
                if ($resultat > 335 /* && $resultat < 365 /*|| ($dernierEntretien->getkmEntretien() + $interval)  >  "km actuel"*/ ) 
                {
                    $tableauVehiculeEntretien[] = $vehicule;
                }
            }
        }

        return $tableauVehiculeEntretien;
    }
}