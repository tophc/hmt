<?php

namespace App\Service\Administration;

use App\Repository\RequeteRepository;
use App\Repository\ChauffeurRepository;
use App\Repository\LogistiqueRepository;
use App\Repository\SecretariatRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Administration\AdministrationUserService;

class AdministrationStatistiqueService 
{
    private $repoRequete;
    private $repoChauffeur;
    private $repoSecretariat;
    private $repoLogistique;
    private $administrationUserService;

    /**
     *  Constructeur
     *
     * @param RequeteRepository $repoRequete
     * @param ChauffeurRepository $repoChauffeur
     * @param SecretariatRepository $repoSecretariat
     * @param LogistiqueRepository $repoLogistique
     * @param AdministrationUserService $administrationUserService
     */
    public function __construct(RequeteRepository $repoRequete, ChauffeurRepository $repoChauffeur, SecretariatRepository $repoSecretariat, LogistiqueRepository $repoLogistique, AdministrationUserService $administrationUserService)
    {
        $this->repoRequete                  = $repoRequete;
        $this->repoChauffeur                = $repoChauffeur;
        $this->repoSecretariat              = $repoSecretariat;
        $this->repoLogistique               = $repoLogistique;
        $this->administrationUserService    = $administrationUserService;
    }

    /**
     * Renvoi un tableau avec toutes les statistiques du dashboard
     * 
     * @return array
     */
    public function getStatistique(): array
    {
        $requeteOuverteLogistique   = $this->getCountRequeteOuverteLogistique();
        $requeteOuverteSecretariat  = $this->getCountRequeteOuverteSecretariat();          
        $chauffeurUser              = $this->getcountChauffeurUser();
        $secretariatUser            = $this->getcountSecretariatUser();
        $logistiqueUser             = $this->getcountLogistiqueUser();

        return compact('requeteOuverteLogistique', 'requeteOuverteSecretariat', 'chauffeurUser', 'secretariatUser', 'logistiqueUser' );
    }

    /**
     * Retourne le nombre de requêtes ouvertes par le service "Secretariat" à destination de "administration"
     *
     * @return int
     */
    public function getCountRequeteOuverteSecretariat(): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'administration', 'requerantRequete' => 'secretariat'] );
        
        return count($requetesEnCours); 
    }
     
    /**
     * Retourne le nombre de requêtes ouvertes par le service "logistique" à destination de "administration"
     *
     * @return int
     */
    public function getCountRequeteOuverteLogistique(): int
    {
        $requetesEnCours = $this->repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'administration', 'requerantRequete' => 'logistique'] );
        
        return count($requetesEnCours); 
    }

    /**
     * Retourne un tableau de 'integer' 
     *
     * @return array
     */
    public function getcountChauffeurUser(): array
    {
        $total      = count($this->repoChauffeur->findAll());
        $disabled   = count($this->administrationUserService->getUserByRole('chauffeur', 'ROLE_DISABLED'));
        $enabled    = count($this->administrationUserService->getEnabledUser('chauffeur', 'ROLE_DISABLED'));
        $reset      = count($this->administrationUserService->getUserByRole('chauffeur', 'ROLE_NEW_USER'));
        $valide      = count($this->administrationUserService->getEnabledUser('chauffeur', 'ROLE_NEW_USER'));

        return compact('total', 'disabled', 'enabled', 'reset', 'valide');
    }

    /**
     * Retourne un tableau de 'integer' 
     *
     * @return array
     */
    public function getcountSecretariatUser(): array
    {
        $total      = count($this->repoSecretariat->findAll());
        $disabled   = count($this->administrationUserService->getUserByRole('secretariat', 'ROLE_DISABLED'));
        $enabled    = count($this->administrationUserService->getEnabledUser('secretariat', 'ROLE_DISABLED'));
        $reset      = count($this->administrationUserService->getUserByRole('secretariat', 'ROLE_NEW_USER'));
        $valide      = count($this->administrationUserService->getEnabledUser('secretariat', 'ROLE_NEW_USER'));

        return compact('total', 'disabled', 'enabled', 'reset', 'valide');
    }

    /**
     * Retourne un tableau de 'integer' 
     *
     * @return array
     */
    public function getcountLogistiqueUser(): array
    {
        $total      = count($this->repoLogistique->findAll());
        $disabled   = count($this->administrationUserService->getUserByRole('logistique', 'ROLE_DISABLED'));
        $enabled    = count($this->administrationUserService->getEnabledUser('logistique', 'ROLE_DISABLED'));
        $reset      = count($this->administrationUserService->getUserByRole('logistique', 'ROLE_NEW_USER'));
        $valide      = count($this->administrationUserService->getEnabledUser('logistique', 'ROLE_NEW_USER'));

        return compact('total', 'disabled', 'enabled', 'reset', 'valide');
    }
}