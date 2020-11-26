<?php

namespace App\Service\Chauffeur;

use DateTime;
use DateInterval;
use App\Repository\AmendeRepository;
use App\Repository\RequeteRepository;
use App\Service\TraductionDateService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AffectationRepository;
use App\Service\Chauffeur\ChauffeurAmendeService;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChauffeurStatistiqueService 
{
    private $manager;
    private $translator;
    private $repoRequete;
    private $repoAmende;
    private $repoAffectation;
    private $chauffeurAmendeService;
    private $traductionDateService;

    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     * @param RequeteRepository $repoRequete
     * @param AmendeRepository $repoAmende
     * @param AffectationRepository $repoAffectation
     * @param ChauffeurAmendeService $chauffeurAmendeService
     * @param TraductionDateService $traductionDateService
     */
    public function __construct(EntityManagerInterface $manager, TranslatorInterface $translator, RequeteRepository $repoRequete, AmendeRepository $repoAmende, AffectationRepository $repoAffectation, ChauffeurAmendeService $chauffeurAmendeService, TraductionDateService $traductionDateService)
    {
        $this->manager                  = $manager;  
        $this->translator               = $translator;
        $this->repoRequete              = $repoRequete;
        $this->repoAmende               = $repoAmende;
        $this->repoAffectation          = $repoAffectation;        
        $this->chauffeurAmendeService   = $chauffeurAmendeService;
        $this->traductionDateService    = $traductionDateService;
    }

    /**
     * Renvoi un tableau avec toutes les statistiques du dashboard
     *
     * @param Chauffeur $chauffeur
     * 
     * @return array
     */
    public function getStatistique($chauffeur): array
    {
        $amendeMois                 = $this->getCountAmendeMois($chauffeur);
        $amendeMoisPrecedant1       = $this->getCountAmendeMoisPrecedant1($chauffeur);
        $amendeMoisPrecedant2       = $this->getCountAmendeMoisPrecedant2($chauffeur);
        $requeteOuverteLogistique   = $this->getCountRequeteOuverteLogistique($chauffeur);
        $requeteOuverteSecretariat  = $this->getCountRequeteOuverteSecretariat($chauffeur);
        $affectations               = $this->getAffectationDuJour($chauffeur);
        $requetesEnCours            = $this->getRequeteOuvert($chauffeur);

        return compact('amendeMois', 'amendeMoisPrecedant1', 'amendeMoisPrecedant2', 'requeteOuverteLogistique', 'requeteOuverteSecretariat', 'affectations', 'requetesEnCours');
    }

    /**
     * Renvoi les objets "Affectation" du jour
     *
     * @param Chauffeur $chauffeur
     * 
     * @return array
     */
    public function getAffectationDuJour($chauffeur): array
    {
        $date = new DateTime('today');
        $affectations = $this->repoAffectation->findByDateFuturChauffeur($chauffeur, $date );

        return array_slice($affectations, 0, 5);
    }

    /**
     * Renvoi les objets "Requete" ouvertes
     *
     * @param Chauffeur $chauffeur
     * 
     * @return array
     */
    public function getRequeteOuvert($chauffeur): array
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'chauffeur' => $chauffeur], [], 5 ); 
        
        return $requetesEnCours;
    }

    /**
     * Renvoi le nombre d'amende du mois en cours
     *
     * @param Chauffeur $chauffeur
     * 
     * @return array
     */
    public function getCountAmendeMois($chauffeur): array
    {
        $date = new DateTime('today');
        list($year,$month) = explode("/",  $date->format('Y/m')) ;
        
        $mois = date_format($date, 'M') ;
        $mois = $this->traductionDateService->traductionDateMoisCourt($mois);
        
        $count = count($this->chauffeurAmendeService->getAmendeByChauffeur($chauffeur, $month, $year));
        
        return array('mois' => $mois, 'count' => $count, 'month' => $month, 'year' => $year);  
    }

    /**
     * Renvoi le nombre d'amende du mois en cours -1 
     *
     * @param Chauffeur $chauffeur
     * 
     * @return array
     */
    public function getCountAmendeMoisPrecedant1($chauffeur): array
    {
        $date =  ($date = new DateTime('today'))->sub(new DateInterval('P1M'));
        list($year,$month) = explode("/",  $date->format('Y/m')) ;

        $mois = date_format($date, 'M') ;
        $mois = $this->traductionDateService->traductionDateMoisCourt($mois);
        
        $count = count($this->chauffeurAmendeService->getAmendeByChauffeur($chauffeur, $month, $year));
        
        return array('mois' => $mois, 'count' => $count, 'month' => $month, 'year' => $year);
    }    

    /**
     * Renvoi le nombre d'amende du mois en cours -2 
     *
     * @param Chauffeur $chauffeur
     * 
     * @return array
     */
    public function getCountAmendeMoisPrecedant2($chauffeur): array
    {
        $date =  ($date = new DateTime('today'))->sub(new DateInterval('P2M'));
        list($year,$month) = explode("/",  $date->format('Y/m')) ;
        $mois = date_format($date, 'M') ;
        $mois = $this->traductionDateService->traductionDateMoisCourt($mois);
        
        $count = count($this->chauffeurAmendeService->getAmendeByChauffeur($chauffeur, $month, $year));
        
        return array('mois' => $mois, 'count' => $count, 'month' => $month, 'year' => $year);
    }    

    /**
     * Renvoi le nombre d'objet "Requete" de l'objet "Chauffeur" pour le service "Logistique"
     *
     * @param Chauffeur $chauffeur
     * 
     * @return int
     */
    public function getCountRequeteOuverteLogistique($chauffeur): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'logistique', 'chauffeur' => $chauffeur] );
        
        return count($requetesEnCours);   
    }

    /**
     * Renvoi le nombre d'objet "Requete" de l'objet "Chauffeur" pour le service "Secretariat"
     *
     * @param Chauffeur $chauffeur
     * 
     * @return int
     */
    public function getCountRequeteOuverteSecretariat($chauffeur): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'secretariat', 'chauffeur' => $chauffeur] );
        
        return count($requetesEnCours); 
    }
}