<?php

namespace App\Service\Secretariat;

use DateTime;
use DateInterval;
use App\Repository\AmendeRepository;
use App\Repository\RequeteRepository;
use App\Service\TraductionDateService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PermisConduireRepository;
use App\Repository\ControleTechniqueRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Secretariat\SecretariatVehiculeService;

class SecretariatStatistiqueService 
{
    private $manager;
    private $translator;
    private $repoPermisConduire;
    private $repoAmende;
    private $repoRequete;
    private $traductionDateService;
    private $repoControleTechnique;
    private $secretariatVehiculeService;

    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     * @param PermisConduireRepository $repoPermisConduire
     * @param AmendeRepository $repoAmende
     * @param RequeteRepository $repoRequete
     * @param TraductionDateService $traductionDateService
     * @param ControleTechniqueRepository $repoControleTechnique
     * @param SecretariatVehiculeService $secretariatVehiculeService
     */
    public function __construct(EntityManagerInterface $manager, TranslatorInterface $translator, PermisConduireRepository $repoPermisConduire, AmendeRepository $repoAmende, RequeteRepository $repoRequete, TraductionDateService $traductionDateService, ControleTechniqueRepository $repoControleTechnique, SecretariatVehiculeService $secretariatVehiculeService)
    {
        $this->manager                      = $manager;
        $this->translator                   = $translator;
        $this->repoPermisConduire           = $repoPermisConduire;
        $this->repoAmende                   = $repoAmende;
        $this->repoRequete                  = $repoRequete;
        $this->traductionDateService        = $traductionDateService;
        $this->repoControleTechnique        = $repoControleTechnique;
        $this->secretariatVehiculeService   = $secretariatVehiculeService;
    }

    /**
     * Retourne un tableau avec toutes les statistiques du dashboard
     *
     * @return array
     */
    public function getStatistique(): array
    {
        $amendeMois                 = $this->getCountAmendeMois();
        $amendeMoisPrecedant1       = $this->getCountAmendeMoisPrecedant1();
        $amendeMoisPrecedant2       = $this->getCountAmendeMoisPrecedant2(); 
        $permisConduireBientot      = $this->getCountPermisConduireBientot();
        $permisConduireExpire       = $this->getCountPermisConduireExpire();
        $requeteOuverteLogistique   = $this->getCountRequeteOuverteLogistique();
        $requeteOuverteChauffeur    = $this->getCountRequeteOuverteChauffeur();
        $requeteOuverteSecretariat  = $this->getCountRequeteOuverteSecretariat();
        $controleTechniqueRefuse    = $this->getCountVehiculeControleTechniqueRefuse();
        $controleTechniqueExpire    = $this->getCountVehiculeControleTechniqueExpire();
        $controleTechniqueBientot   = $this->getCountVehiculeControleTechniqueBientot();
        $entretien                  = $this->getCountVehiculeEntretien();

        return compact('amendeMois', 'amendeMoisPrecedant1', 'amendeMoisPrecedant2','permisConduireBientot', 'permisConduireExpire', 'requeteOuverteLogistique', 'requeteOuverteChauffeur', 'requeteOuverteSecretariat', 'controleTechniqueRefuse', 'controleTechniqueExpire', 'controleTechniqueBientot', 'entretien' );
    }

    /**
     * Retourne le nombre d'amende du mois en cours et la traduction du mois en cours
     *
     * @return array
     */
    public function getCountAmendeMois(): array
    {
        $dateToday = new DateTime('today');
        list($year,$month,$day) = explode("/", $dateToday->format('Y/m/d'));
         
        $mois = date_format($dateToday, 'M') ;
        $mois = $this->traductionDateService->traductionDateMoisCourt($mois);
        $count = count($this->repoAmende->getAmendeMois($month, $year));
        
        return array('mois' => $mois, 'count' => $count, 'month' => $month, 'year' => $year);
    }

    /**
     * Retourne le nombre d'amende du mois en cours -1 et la traduction du mois en cours -1
     *
     * @return array
     */
    public function getCountAmendeMoisPrecedant1(): array
    {
        $dateToday= new DateTime('today');
        $dateToday->sub(new DateInterval('P1M'));
        list($year,$month,$day) = explode("/", $dateToday->format('Y/m/d'));

        $mois = date_format($dateToday, 'M') ;
        $mois = $this->traductionDateService->traductionDateMoisCourt($mois);
        $count = count($this->repoAmende->getAmendeMois($month, $year));

        return array('mois' => $mois, 'count' => $count, 'month' => $month, 'year' => $year);
    }

    /**
     * Retourne le nombre d'amende du mois en cours -2 et la traduction du mois en cours -2
     *
     * @return array
     */
    public function getCountAmendeMoisPrecedant2(): array
    {
        $dateToday = new DateTime('today');
        $dateToday->sub(new DateInterval('P2M'));
        list($year,$month) = explode("/", $dateToday->format('Y/m'));

        $mois = date_format($dateToday, 'M') ;
        $mois = $this->traductionDateService->traductionDateMoisCourt($mois);
        $count = count($this->repoAmende->getAmendeMois($month, $year));

        return array('mois' => $mois, 'count' => $count, 'month' => $month, 'year' => $year);
    }

    /**
     * Retourne le nombre d'objets "PermisConduire" bientôt arrivés à échéance
     *
     * @return int
     */
    public function getCountPermisConduireBientot(): int
    {
        return count($this->repoPermisConduire->getPermisConduireBientot());
    }

    /**
     * Retourne le nombre d'objets "PermisConduire" expirés
     *
     * @return int
     */
    public function getCountPermisConduireExpire(): int
    {
        return count($this->repoPermisConduire->getPermisConduireExpire());
    }

    /**
     * Retourne le nombre de requêtes ouvertes par le service "Chauffeur" à destination de "Secretariat"
     *
     * @return int
     */
    public function getCountRequeteOuverteChauffeur(): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'secretariat', 'requerantRequete' => 'chauffeur'] );

        return count($requetesEnCours);
    }

    /**
     * Retourne le nombre de requêtes ouvertes par le service "Logistique" à destination de "Secretariat"
     *
     * @return int
     */
    public function getCountRequeteOuverteLogistique(): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'secretariat', 'requerantRequete' => 'logistique'] );

        return count($requetesEnCours);
    }

    /**
     * Retourne le nombre de requêtes ouvertes par le service "Secretariat" à destination de "Logistique"
     *
     * @return int
     */
    public function getCountRequeteOuverteSecretariat(): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => ['logistique', 'administration'], 'requerantRequete' => 'secretariat'] );
        
        return count($requetesEnCours); 
    }

    /**
     * Retourne le nombre d'objet "Vehicule" active possédant un objet "ControleTechnique" dont le statutCotroleTechnique est à "true" 
     *
     * @return int
     */
    public function getCountVehiculeControleTechniqueRefuse(): int
    {
        $vehicules = $this->secretariatVehiculeService->getVehiculeControleTechniqueRefuse();

        return count($vehicules);
    }
    
    /**
     * Retourne le nombre d'objet "Vehicule" active possédant un objet "ControleTechnique" dont le "dateCotroleTechnique"  est dépassée 
     *
     * @return int
     */
    public function getCountVehiculeControleTechniqueExpire(): int
    {
        $vehicules = $this->secretariatVehiculeService->getVehiculeControleTechniqueExpire();

        return count($vehicules);
    }

    /**
     * Retourne le nombre d'objet "Vehicule" active possédant un objet "ControleTechnique" dont le "dateCotroleTechnique" arrive bientôt à échéance
     *
     * @return int
     */
    public function getCountVehiculeControleTechniqueBientot(): int
    {
        $vehicules = $this->secretariatVehiculeService->getVehiculeControleTechniqueBientot();

        return count($vehicules);
    }

    /**
     * Retourne le nombre d'objet "Vehicule" active dont le "dateEntretien" du dernier entretien data de plus d'un an
     *
     * @return int
     */
    public function getCountVehiculeEntretien(): int
    {
        $vehicules = $this->secretariatVehiculeService->getVehiculeEntretien();

        return count($vehicules);
    }
}