<?php

namespace App\Service\Administration;

use App\Repository\ChauffeurRepository;
use App\Repository\LogistiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SecretariatRepository;

class AdministrationUserService 
{
    private $manager;
    private $repoChauffeur;
    private $repoSecretariat;
    private $repoLogistique;
    
    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     * @param ChauffeurRepository $repoChauffeur
     * @param SecretariatRepository $repoSecretariat
     * @param LogistiqueRepository $repoLogistique
     */
    public function __construct(EntityManagerInterface $manager, ChauffeurRepository $repoChauffeur, SecretariatRepository $repoSecretariat, LogistiqueRepository $repoLogistique)
    {
        $this->manager          = $manager;   
        $this->repoChauffeur    = $repoChauffeur;
        $this->repoSecretariat  = $repoSecretariat;
        $this->repoLogistique   = $repoLogistique;
    }
     
    /**
     *  Retourne les objets 'user' ("CHAUFFEUR", "Secretariat", "Logistique") avec le $role 'ROLE_DISABLED' ou 'ROLE_NEW_USER'
     *
     * @param string $service
     * @param string $role
     * 
     * @return array
     */
    public function getUserByRole($service, $role): array
    {
        $allUsers = array();
        $tableauUsers = array();
    
        if ($service === 'chauffeur' )
        {
            $allUsers = $this->repoChauffeur->findAll();
        }
        else if ($service === 'secretariat' )
        {
            $allUsers = $this->repoSecretariat->findAll();
        }
        else /* if ($service === 'logistique' )*/
        {
            $allUsers = $this->repoLogistique->findAll();
        }
      
        if (! empty($allUsers))
        {
            foreach($allUsers as $user)
            {
                if (in_array($role, $user->getRoles()))
                {
                    $tableauUsers[] = $user;
                }
            }
        }

        return $tableauUsers;
    }

    /**
     * Retourne les objets 'user' ("CHAUFFEUR", "Secretariat", "Logistique") sans le $role 'ROLE_DISABLED' ou 'Role_NEW_USER'
     *
     * @param string $service
     * @param string $role
     * @return array
     */
    public function getEnabledUser($service, $role): array
    {
        $allUsers = array();
        $tableauUsers = array();
    
        if ($service === 'chauffeur' )
        {
            $allUsers = $this->repoChauffeur->findAll();
        }
        else if ($service === 'secretariat' )
        {
            $allUsers = $this->repoSecretariat->findAll();
        }
        else /* if ($service === 'logistique' )*/
        {
            $allUsers = $this->repoLogistique->findAll();
        }
        
        if (! empty($allUsers))
        {
            foreach($allUsers as $user)
            {
                if (! in_array($role, $user->getRoles()) && ! in_array('ROLE_DISABLED', $user->getRoles()) ) 
                {
                    $tableauUsers[] = $user;
                }
            }
        }

        return $tableauUsers;
    }
}