<?php

namespace App\Service\Logistique;

use App\Repository\RequeteRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ChauffeurRepository;
use App\Repository\SuiviColisRepository;
use App\Repository\AffectationRepository;
use Doctrine\ORM\EntityManagerInterface; 

class LogistiqueStatistiqueService 
{
    private $manager;
    private $repoRequete;
    private $repoSuiviColis;
    private $logistiqueChauffeurService;
    private $logistiqueVehiculeService;
    //private $repoChauffeur;
    
    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     * @param RequeteRepository $repoRequete
     * @param SuiviColisRepository $repoSuiviColis
     * @param LogistiqueChauffeurService $logistiqueChauffeurService
     * @param LogistiqueVehiculeService $logistiqueVehiculeService
     * 
     */
    public function __construct(/*ChauffeurRepository $repoChauffeur,*/EntityManagerInterface $manager, RequeteRepository $repoRequete, SuiviColisRepository $repoSuiviColis, LogistiqueChauffeurService $logistiqueChauffeurService, LogistiqueVehiculeService $logistiqueVehiculeService)
    {
        $this->manager                      = $manager;
        $this->repoRequete                  = $repoRequete;
        $this->repoSuiviColis               = $repoSuiviColis;
        $this->logistiqueChauffeurService   = $logistiqueChauffeurService;
        $this->logistiqueVehiculeService    = $logistiqueVehiculeService;
        //$this->repoChauffeur              = $repoChauffeur;
    }
    
    /**
     * Renvoi un tableau avec toutes les statistiques du dashboard
     *
     * @return array
     */
    public function getStatistique(): array
    {
        $requeteOuverteLogistique   = $this->getCountRequeteOuverteLogistique();
        $requeteOuverteChauffeur    = $this->getCountRequeteOuverteChauffeur();
        $requeteOuverteSecretariat  = $this->getCountRequeteOuverteSecretariat();
        $expedition                 = $this->getCountExpedition();
        $express                    = $this->getCountExpress();
        $enlevement                 = $this->getCountEnlevement();
        $expeditionLitige           = $this->getCountExpeditionLitige();
        $enlevementLitige           = $this->getCountEnlevementLitige();
        $chauffeurLibre             = $this->getCountChauffeurLibre();
        $vehiculeLibre              = $this->getCountVehiculeLibre();
        $expeditionHorsTournee      = $this->getCountExpeditionHorsTournee();
        $enlevementHorsTournee      = $this->getCountEnlevementHorsTournee();
        
        return compact('requeteOuverteLogistique', 'requeteOuverteChauffeur', 'requeteOuverteSecretariat', 'expedition', 'express', 'enlevement', 'expeditionLitige', 'enlevementLitige', 'chauffeurLibre', 'vehiculeLibre', 'expeditionHorsTournee', 'enlevementHorsTournee');
    }

    /**
     * Retourne le nombre de requêtes ouvertes par le service "Chauffeur" à destination de "logistique"
     *
     * @return int
     */
    public function getCountRequeteOuverteChauffeur(): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'logistique', 'requerantRequete' => 'chauffeur'] );
        
        return count($requetesEnCours);   
    }

    /**
     * Retourne le nombre de requêtes ouvertes par le service "Secretariat" à destination de "logistique"
     *
     * @return int
     */
    public function getCountRequeteOuverteSecretariat(): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'logistique', 'requerantRequete' => 'secretariat'] );
        
        return count($requetesEnCours); 
    }
     
    /**
     * Retourne le nombre de requêtes ouvertes par le service "logistique" à destination de "Secretariat"
     *
     * @return int
     */
    public function getCountRequeteOuverteLogistique(): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => ['secretariat' ,'administration'], 'requerantRequete' => 'logistique'] );
        
        return count($requetesEnCours); 
    }

    /**
     * Retourne le nombre d'objet "Colis" du jour en cours et de type 1 (expédition)
     *
     * @return int
     */
    public function getCountExpedition(): int
    {
        $typeColis = 1;
        
        return $this->repoSuiviColis->listeColisDuJourCount($typeColis);
    }

    /**
     * Retourne le nombre d'objet "Colis" express du jour en cours et de type 1 (expédition)
     *
     * @return int
     */
    public function getCountExpress(): int
    {
        $typeColis = 1;

        return $this->repoSuiviColis->listeColisExpressDuJourCount($typeColis);
    }

    /**
     *  Retourne le nombre d'objet "Colis" du jour en cours et de type 0 (enlevement)
     *
     * @return int
     */
    public function getCountEnlevement(): int
    {
        $typeColis = 0;
        
        return $this->repoSuiviColis->listeColisDuJourCount($typeColis);
    }

    /**
     * Retourne le nombre d'objet "Colis" de type 1 (expédition) aynant un litige (008) en cours
     *
     * @return int
     */
    public function getCountExpeditionLitige(): int
    { 
        $tableauColisNonFiltre = array();
        $colisFiltre = array();
        
        //On récupère tous les objets "SuiviColis" ayant le codeEtat '008'
        $suiviColis =   $this->manager->createQuery(
                            "   SELECT s
                                FROM App\Entity\SuiviColis s
                                JOIN s.etat e
                                JOIN s.colis c
                                WHERE e.codeEtat = '008'
                                AND c.typeColis = 1
                                AND e.codeEtat != '999'
                                GROUP BY s.colis
                            "
                            )   
                            ->getResult();

        //Pour chaques objets "SuiviColis" on récupère l'objet 'Colis'                 
        foreach ($suiviColis as $suivi)
        {
            $colis = $suivi->getColis();
            $tableauColisNonFiltre [] = clone $colis;
        }
        // Pour chaques objets 'Colis' 
        foreach ($tableauColisNonFiltre as $colis)
        {
            $tableauEtatParColis = [];
            //Pour chaques objets 'SuiviColis' de l'objet 'Colis on récupere le codeEtat 
            foreach ($colis->getSuiviColis() as $suivis)
            {
                //On ajoute le codeEtat dans un tableau 
                $tableauEtatParColis[] = $suivis->getEtat()->getCodeEtat();
            } 
            //On verifie si l'obet "Colis" n'a pas été livré (999)
            if (! (in_array("999", $tableauEtatParColis)))
            {
                $colisFiltre [] = clone $colis;       
            } 
        }

        return count($colisFiltre);
    }

    /**
     * Retourne le nombre d'objet "Colis" de type 0 (enlevement) aynant un litige (008) en cours
     *
     * @return int
     */
    public function getCountEnlevementLitige(): int
    { 
        $tableauColisNonFiltre = array();
        $colisFiltre = array();
        //On récupère tous les objets "SuiviColis" ayant le codeEtat '008'
        $suiviColis =  $this->manager->createQuery(
                            "   SELECT s
                                FROM App\Entity\SuiviColis s
                                JOIN s.etat e
                                JOIN s.colis c
                                WHERE e.codeEtat = '008'
                                AND c.typeColis = 0
                                GROUP BY s.colis
                            "
                            )->getResult();
                            
        //Pour chaques objets "SuiviColis" on récupère l'objet 'Colis'                 
        foreach ($suiviColis as $suivi)
        {
            $colis = $suivi->getColis();
            $tableauColisNonFiltre [] = clone $colis;
        }
        // Pour chaques objets 'Colis' 
        foreach ($tableauColisNonFiltre as $colis)
        {
            $tableauEtatParColis = [];
            //Pour chaques objets 'SuiviColis' de l'objet 'Colis on récupere le codeEtat 
            foreach ($colis->getSuiviColis() as $suivis)
            {
                //On ajoute le codeEtat dans un tableau 
                $tableauEtatParColis[] = $suivis->getEtat()->getCodeEtat();
            } 
            //On verifie si l'obet "Colis" n'a pas été livré (999)
            if (! (in_array("999", $tableauEtatParColis)))
            {
                $colisFiltre [] = clone $colis;       
            } 
        }
        
        return count($suiviColis);
    }

    public function getCountExpeditionHorsTournee()
    {
        $typeColis = 1;
        
        return $this->repoSuiviColis->listeColisHorsTournee($typeColis);
    }

    public function getCountEnlevementHorsTournee()
    {
        $typeColis = 0;
        
        return $this->repoSuiviColis->listeColisHorsTournee($typeColis);
    }

    /**
     * Retourne le nombre d'objets "Chauffeur" sans affectation le jour en cours
     *
     * @return int
     */
    public function getCountChauffeurLibre(): int
    {
        return count($this->logistiqueChauffeurService->getChauffeurLibre());
        //return count($this->repoChauffeur->getChauffeurLibre());
    }
    
    /**
     * Retourne le nombre d'objets "Vehicule" sans affectation le jour en cours
     *
     * @return int
     */
    public function getCountVehiculeLibre(): int
    {
       return count($this->logistiqueVehiculeService->getVehiculeLibre());
    }
}