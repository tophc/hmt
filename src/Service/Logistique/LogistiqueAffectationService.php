<?php

namespace App\Service\Logistique;

use DateTime;
use DateInterval;
use App\Entity\Affectation;
use App\Repository\TourneeRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ChauffeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AffectationRepository;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogistiqueAffectationService extends AbstractController
{
    private $manager;
    private $repoAffectation;
    private $repoChauffeur;
    private $repoVehicule;
    private $repoTournee;
    private $session;
    private $translator;
    private $requestStack;

    /**
     * Constructeur
     *
     * @param EntityManagerInterface $manager
     * @param AffectationRepository $repoAffectation
     * @param ChauffeurRepository $repoChauffeur
     * @param VehiculeRepository $repoVehicule
     * @param TourneeRepository $repoTournee
     * @param SessionInterface $session
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $manager, AffectationRepository $repoAffectation, ChauffeurRepository $repoChauffeur, VehiculeRepository $repoVehicule, TourneeRepository $repoTournee, SessionInterface $session, TranslatorInterface $translator, RequestStack $requestStack )
    {
        $this->manager = $manager;
        $this->repoAffectation = $repoAffectation;
        $this->repoChauffeur = $repoChauffeur;
        $this->repoVehicule = $repoVehicule;
        $this->repoTournee = $repoTournee;
        $this->session= $session;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    /**
     * Effectue les contrôles de validité lors de la création d'un objet "Affectation"
     *
     * @param Affectation $affectation
     * 
     * @return bool
     */
    public function affectationUniqueControle($affectation): bool
    {
        $erreurAffectation = false;
        $date = clone $affectation->getDateAffectation();
        
        /********************************/
        /**** Champs dateAffectation ****/
        /********************************/
        
        // On vérifie que la 'dateAffectation' n'est pas anterieure à la date du jour
        if ($affectation->getDateAffectation() <  new DateTime('today'))
        {                  
           $erreurAffectation = true;                                

            $this->addFlash(
                'dateAffectation', 
                $this->translator->trans('No assignment before').' : '. Date('d-m-Y') 
            );   
        } 
        
        // On vérifie que la 'dateAffectation' n'est pas un samedi ou un dimanche
        if((date_format($date , 'D') === "Sat") | (date_format($date , 'D') === "Sun"))
        {
            $erreurAffectation = true; 
            
            $this->addFlash(
                'dateAffectation', 
                $this->translator->trans('No weekend assignments')
            );   
        }

        /**************************/               
        /**** Champs chauffeur ****/
        /**************************/

        $chauffeur = $affectation->getChauffeur();
        $affect = $this->repoAffectation->findOneBy(['chauffeur' => $chauffeur, 'dateAffectation' => $date]);
        
        if ($affect != null)
        {   
            $erreurAffectation = true;

            $this->addFlash(
                'chauffeur', 
                $this->translator->trans('The driver is already assigned for this date')
            );   
        }                               
        
        // Pas nécessaire mais on sait jamais
        // On vérifie si le chauffeur est actif : si non, message + $erreurAffectation = true
        if (! $chauffeur->getStatutChauffeur())
        {
            $erreurAffectation = true; 

            $this->addFlash(
                'chauffeur', 
                $this->translator->trans('Driver is disabled')
            );       
        }

        // On vérifie la validité du permis de conduire
        if ($this->affectationDateValPermisControle($affectation)) 
        {
            $erreurAffectation = true;
            
            $this->addFlash(
                'chauffeur', 
                $this->translator->trans("Driver's license has expired")
            );  
        } 

        /*************************/               
        /**** Champs vehicule ****/
        /*************************/
        
        $vehicule = $affectation->getVehicule();
        $affect = $this->repoAffectation->findOneBy(['vehicule' => $vehicule, 'dateAffectation' => $date]);

        // On vérifie si le vehicule a déja une assignation à cette date : si oui, message + $erreurAffectation = true
        if ($affect != null )
        {
            $erreurAffectation = true;

            $this->addFlash(
                'vehicule', 
                $this->translator->trans('Vehicle is already assigned for this date')
            );                              
        } 
        // Pas nécessaire mais on sait jamais      
        // On verifie si le vehicule est actif : si non, message + $erreurAffectation = true
        if (! $vehicule->getStatutVehicule())
        {
            $erreurAffectation = true; 
            $this->addFlash(
                'vehicule', 
                $this->translator->trans('Vehicle is disabled')
            );       
        } 
        
        // On vérifie la correspondance CapaciteModeleVehicule/categoriePermis
        if ($this->affectationCategoriePermisControle($affectation))
        {
            $erreurAffectation = true;
        } 
       
        return $erreurAffectation;
    }

    /**
     * Effectue les pré-contrôles de validité lors de la création de plusieurs objets "Affectation" à la volée
     *
     * @param Affectation $affectation
     * @param Date $dateDebut
     * @param Date $dateFin
     * 
     * @return bool
     */
    public function affectationMiltiplePrecontrole($affectation, $dateDebut, $dateFin): bool
    {
        $erreurAffectation = false;     

        // On vérifie si "dateFin" est inferieure ou égale à "dateAffectation" :  : si oui, message + $erreurAffectation = true
        if ($dateFin <=  $dateDebut )
        {                  
            $erreurAffectation = true;                                
            
            $this->addFlash(
                'dateFin', 
                $this->translator->trans('End date must be greater than').' : '. $dateDebut->format('Y-m-d')
            );                  
        }

        // On vérifie si la dateAffectation est antérieur à la date du jour : si oui, message + $erreurAffectation = true
        if ($affectation->getDateAffectation() <  new DateTime('today'))
        {                  
            $erreurAffectation = true;                                
            
            $this->addFlash(
                'dateAffectation', 
                $this->translator->trans('No assignment before').' : '.  Date('d-m-Y')
            );                                         
        }

        // On vérifie si le chauffeur est actif : si non, message + $erreurAffectation = true
        if (! $affectation->getChauffeur()->getStatutChauffeur())
        {
            $erreurAffectation = true; 
            $this->addFlash(
                'chauffeur', 
                $this->translator->trans('Driver is disabled')
            );                 
        }

        // On vérifie la validité du permis de conduire
        //if ($this->affectationDateValPermisControle($affectation)) $erreurAffectation = true; 

        // On vérifie la validité du permis de conduire
        if ($this->affectationDateValPermisControle($affectation)) 
        {
            $erreurAffectation = true;
            
            $this->addFlash(
                'chauffeur', 
                $this->translator->trans("Driver's license has expired")
            );  
        
        } 
        // On verifie si le vehicule est actif : si non, message + $erreurAffectation = true
        if (! $affectation->getVehicule()->getStatutVehicule())
        {
            $erreurAffectation = true; 
            $this->addFlash(
                'vehicule', 
                $this->translator->trans('Vehicle is disabled')
            );       
        }  
        // On vérifie la validité du permis de conduire
        if ($this->affectationDateValPermisControle($affectation)) 
        {
            $erreurAffectation = true;
            
            $this->addFlash(
                'chauffeur', 
                $this->translator->trans("Driver's license has expired")
            );  
        } 

        return $erreurAffectation;
    }

    /**
     * Effectue les contrôles de validité lors de la création de plusieurs objets "Affectation" à la volée
     *
     * @param Affectation $affectation
     * @param Date $dateDebut
     * @param Date $dateFin
     * 
     * @return array
     */
    public function affectationMultipleControle($affectation, $dateDebut, $dateFin): array
    {
        $erreurValPermis = false;
        // Affectations invalides
        $affectationsAbandonnees = array();
        // Les id des affectations invalides
        $listeId = array();
        // Affectations valides
        $tabAffectations = array();

        for ($i = $dateDebut ; $i <= $dateFin ; $i->add(new DateInterval('P1D')))
        { 
            $erreurAffectation  = false;
           
            //Exclure les affectations du samedi et dimanche 
            if((date_format($i, 'D') !== "Sat") && (date_format($i, 'D') != "Sun"))
            {                                                                                                       
                // Pour chaque objet "Affectation", on fait les controles: chauffeur, vehicule  
                
                /**************************/               
                /**** Champs chauffeur ****/
                /**************************/

                // On vérifie si le chauffeur a déja une affectation à cette date : si oui, message + $erreurAffectation = true
                $chauffeur = $affectation->getChauffeur();
                $affectations = $this->repoAffectation->findOneBy(['dateAffectation' => $i, 'chauffeur' =>  $chauffeur] );

                if (!empty($affectations))
                {                      
                    $erreurAffectation = true;   
                    $affectationsAbandonnees [] = clone $affectations;  
                }

                // On vérifie la validité du permis à la date '$i'
                $affectationTemp = clone $affectation;
                $affectationTemp->setDateAffectation($i); 
                if ($this->affectationDateValPermisControle($affectationTemp)) 
                {
                    $erreurAffectation = true;
                    $erreurValPermis = true;
                }

                /*************************/               
                /**** Champs vehicule ****/
                /*************************/

                // On vérifie si le véhicule a déja une affectation à cette date : si oui, message + $erreurAffectation = true
                $vehicule = $affectation->getVehicule();
                $affectations = $this->repoAffectation->findOneBy(['dateAffectation' => $i, 'vehicule' =>  $vehicule] );

                if (!empty($affectations))
                {
                    $erreurAffectation = true;   
                    $affectationsAbandonnees [] = clone $affectations;                
                }
                
                // S'il n'y a pas d'erreurs, on persiste l'objet "Affectation"
                if (!$erreurAffectation)
                {
                    $affectationMultiple = new Affectation();
                    $affectationMultiple->setDateAffectation(clone $i)
                                        ->setChauffeur($affectation->getChauffeur())
                                        ->setVehicule($affectation->getVehicule())
                                        ->setTournee($affectation->getTournee())
                    ;   
                    
                    $this->manager->persist($affectationMultiple);  
                    $this->manager->flush();

                    $tabAffectations[] = clone $affectationMultiple;
                } 
            } 
        }       

        // On supprime les doublons du tableau des affections qui n'on pas été persistées en DB et on ne garde que les IdAffectations pour les transmetre dans une variable de session
        if (! empty($affectationsAbandonnees))
        {      
            foreach ($affectationsAbandonnees as $objetAffectation)
            {
                if (! in_array($objetAffectation->getId(), $listeId))
                {
                    $listeId[]  = $objetAffectation->getId();
                }
            }   
            // On stocke en session les "idAffectation", le "chauffeur" et le "vehicule" pour les transmetre à la route logistique_affectation_liste_Invalide   
            // Permet de mettre en évidence l'élement qui pose problème
            // Permet de mettre en évidence l'affectation déjà présente
            $this->session->set('chauffeur' , $affectation->getChauffeur()->getId());
            $this->session->set('vehicule' , $affectation->getVehicule()->getId());
            $this->session->set('tournee' , $affectation->getTournee()->getId());
            
            // On stocke en session les "idAffectation" pour les transmetre à la route logistique_affectation_liste_Invalide 
            // Permetra d'afficher les affectations invalides dans une autre vue
            $this->session->set('idAffectation' , $listeId);
        }

        if (empty($tabAffectations))
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('No assignment has been added').' !'
            );
        }
        else
        {
            $this->addFlash(
                'success', 
                $this->translator->trans('The multiple assignment has been added'). ' !'
            );
        }

        if ($erreurValPermis)
        {
            $dateValPermis = ($affectation->getchauffeur()->getPermisConduire()->getDateValPermisConduire())->format('d.m.Y');
            $this->addFlash(
                'warning', 
                $this->translator->trans('The driver\'s license is valid until')."$dateValPermis" .' !'
            );
        }

        return ['tabAffectations' => $tabAffectations ,'listeId' => $listeId ];
    }

    /**
     * Effectue les contrôles de validité lors de la modification d'un objet "Affectation"
     *
     * @param Affectation $affectation
     * 
     * @return bool
     */
    public function affectationModifierControle($oldAffectation, $affectation): bool
    {   
        $erreurAffectation = false;

        // Le chauffeur et le véhicule de départ
        $chauffeurOld = clone $oldAffectation->getChauffeur();    
        $vehiculeOld = clone $oldAffectation->getVehicule();

        $date = clone $oldAffectation->getDateAffectation();

        /**************************/               
        /**** Champs chauffeur ****/
        /**************************/

        $chauffeurNew =  $affectation->getChauffeur();
        $affect = $this->repoAffectation->findOneBy(['chauffeur' => $chauffeurNew, 'dateAffectation' => $date]);

        // on commance par vérifier si le chauffeur (nouveau) a une affectation à cette date : 
        if ($affect != null)
        {   
            // Ensuite on vérifie si le chauffeur a été modifié  
            if ($chauffeurOld->getId() !=  $chauffeurNew->getId())
            {
                $erreurAffectation = true;

                $this->addFlash(
                    'chauffeur', 
                    $this->translator->trans('The driver is already assigned for this date')
                );   
            }                               
        }

        // Pas nécessaire mais on sait jamais
        // On vérifie si le chauffeur n'est pas actif :  message + $erreurAffectation = true
        if (! $chauffeurNew->getStatutChauffeur())
        {
            $erreurAffectation = true; 
            $this->addFlash(
                'chauffeur', 
                $this->translator->trans('Driver is disabled')
            );       
        }
        
        // On vérifie la validité du permis de conduire
        if ($this->affectationDateValPermisControle($affectation)) 
        {
            $erreurAffectation = true;
            
            $this->addFlash(
                'chauffeur', 
                $this->translator->trans("Driver's license has expired")
            );  
        } 

        /*************************/               
        /**** Champs vehicule ****/
        /*************************/
        
        $vehiculeNew = $affectation->getVehicule();
        $affect = $this->repoAffectation->findOneBy(['vehicule' => $vehiculeNew, 'dateAffectation' => $date]);

        // on commance par vérifie si le vehicule a une affectation à cette date : 
        if ($affect != null )  
        {
            // Ensuite on vérifie si le vehicule a été modifié 
            if ($vehiculeOld->getId() != $vehiculeNew->getId())
            {
                $erreurAffectation = true;

                $this->addFlash(
                    'vehicule', 
                    $this->translator->trans('Vehicle is already assigned for this date')
                );
            }                               
        } 

        // Pas nécessaire mais on sait jamais
        // On vérifie si le vehicule est actif : si non, message + $erreurAffectation = true
        if (! $vehiculeNew->getStatutVehicule())
        {
            $erreurAffectation = true; 
            $this->addFlash(
                'vehicule', 
                $this->translator->trans('Vehicle is disabled')
            );       
        }
        // On vérifie la correspondance CapaciteModeleVehicule/categoriePermis
        if ($this->affectationCategoriePermisControle($affectation)) $erreurAffectation = true;

        return $erreurAffectation;
    }

    /**
     * Permet de vérifier la "dateValPermisConduire" (Karine Desmaret)
     *
     * @param Affectation $affectation
     * 
     * @return bool
     */
    public function affectationDateValPermisControle($affectation): bool
    {
        $erreurPermis = false;

        $dateAffectation = clone $affectation->getDateAffectation();
        $dateValPermisConduire = clone $affectation->getChauffeur()->getPermisConduire()->getDateValPermisConduire();
        
        if ($dateAffectation > $dateValPermisConduire)$erreurPermis = true;
             
        return  $erreurPermis;
    }

    /**
     * Vérifie la correspondance "CategoriePermisConduire" et "Vehicule"
     *
     * @param Affectation $affectation
     * 
     * @return bool
     */
    public function affectationCategoriePermisControle($affectation): bool
    {
        $erreurPermis = false;

        //On récupère les objets "CategoriePermisConduires" de l'objet "PermisConduire" de l'objet "Chauffeur" de l'objet "Affectation"
        $TableauCategoriePermisConduires = array();
        $TableauCategoriePermisConduires = $affectation->getChauffeur()->getPermisConduire()->getCategoriePermisConduires();
                    
        //on récupère les champs "nomCategoriePermisConduire" dans un tableau
        foreach ($TableauCategoriePermisConduires as $categoriePermisConduire)
        {
            $nomCategories[] =  $categoriePermisConduire->getNomCategoriePermisConduire();
        }
        // on recupère la 'mma' du véhicule
        $MMA = $affectation->getVehicule()->getModeleVehicule()->getCapaciteModeleVehicule();
        
        //Ensuite on fait les tests de correspondance véhicule/catégorie de permis
        if ($MMA <= 3500 )
        {
            if (! in_array('B', $nomCategories) && ! in_array('BE', $nomCategories) && ! in_array('B96', $nomCategories) && ! in_array('C1', $nomCategories) && ! in_array('C1E', $nomCategories) && ! in_array('C', $nomCategories) && ! in_array('CE', $nomCategories))  
            {
                $erreurPermis = true;

                $this->addFlash(
                    'vehicule', 
                    $this->translator->trans("This vehicle need a 'B' category")
                );    
            }        
        }
        else if ($MMA > 3500 && $MMA < 7500  )
        {
            if (! in_array('C1', $nomCategories) && ! in_array('C1E', $nomCategories)  && ! in_array('C', $nomCategories) && ! in_array('CE', $nomCategories) )
            {
                $erreurPermis = true;

                $this->addFlash(
                    'vehicule', 
                    $this->translator->trans("This vehicle need a 'C1' category")
                );   
            }     
        }
        else if ($MMA >= 7500 )
        {
            if (! in_array('C', $nomCategories) && ! in_array('CE', $nomCategories)) 
            {
                $erreurPermis = true;

                $this->addFlash(
                    'vehicule', 
                    $this->translator->trans("This vehicle need a 'C' category")
                );  
            }   
        }
        // On sait jamais
        else
        {
            $this->addFlash(
                'vehicule', 
                $this->translator->trans('This vehicle has no MMA')
            );  
            $erreurPermis = true;    
        }

        return  $erreurPermis;
    }

    /**
     * Prépare le fichier json pour la requête Ajax du plugin "dataTables" Affectations
     *
     * @param string $route
     * 
     * @return string
     */
    public function dataTablesAffectationApi($route): string
    {   
        if ($this->requestStack->getCurrentRequest()->getMethod() == 'POST')
        {
            $draw       = intval($this->requestStack->getCurrentRequest()->request->get('draw'));
            $start      = $this->requestStack->getCurrentRequest()->request->get('start');
            $length     = $this->requestStack->getCurrentRequest()->request->get('length');
            $search     = $this->requestStack->getCurrentRequest()->request->get('search');
            $orders     = $this->requestStack->getCurrentRequest()->request->get('order');
            $columns    = $this->requestStack->getCurrentRequest()->request->get('columns');
        }
        else // si la request n'est pas de type POST 
        {
           die; 
        }
          
        // Orders
        foreach ($orders as $key => $order)
        {
            //convertit le numéro de colonne en nom de colonne
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }
        
        // Fait appel au à la méthode du SuiviColisRepository correspondante à la route
        switch($route)
        {
            case 'logistique_affectation_liste':
                $results = $this->repoAffectation->getListeAffectationFuture($start, $length, $orders, $search); 
                $total_objects_count = $this->repoAffectation->listeAffectationFutureCount();
            break; 
            case 'logistique_affectation_archive': 
                $results = $this->repoAffectation->getListeAffectationArchive($start, $length, $orders, $search); 
                $total_objects_count = $this->repoAffectation->listeAffectationArchiveCount();  
            break; 
            case 'logistique_affectation_vehicule_inactif': 
                $results = $this->repoAffectation->getListeAffectationVehiculeInactif($start, $length, $orders, $search); 
                $total_objects_count = $this->repoAffectation->getAffectationVehiculeInactifCount();  
            break; 
            case 'logistique_affectation_chauffeur_inactif': 
                $results = $this->repoAffectation->getListeAffectationChauffeurInactif($start, $length, $orders, $search); 
                $total_objects_count = $this->repoAffectation->getAffectationChauffeurInactifCount();  
            break; 
            default;
                die;   
        }

        $objects = $results["results"];
        // nombre total de $resultat
        $selected_objects_count = count($objects);
        // Nombre total d'objets après le filtrag sur le critère de recherche
        $filtered_objects_count = $results["countResult"];

        $response = '{
            "draw": '.$draw.',
            "recordsTotal": '.$total_objects_count.',
            "recordsFiltered": '.$filtered_objects_count.',
            "data": [';
            
        $i = 0;
        foreach ($objects as $key => $affectation)
        {
            //construit la réponse 'json'
            $response .= '["';

            $j = 0; 
            $nbColumn = count($columns);
            foreach ($columns as $key => $column)
            {
                /// Si soucis ou valeur null,  on returne "-"
                $responseTemp = "-";
    
                switch($column['name'])
                {
                    case 'Date':
                    {
                        $responseTemp = date_format ($affectation->getDateAffectation(),'d.m.Y');
                        break; 
                    }
                    case 'Driver':
                    {
                        $responseTemp = $affectation->getChauffeur()->getNomChauffeur().' '.$affectation->getChauffeur()->getPrenomChauffeur();
                        break; 
                    }
                    case 'Vehicle':
                    {
                        $responseTemp = $affectation->getVehicule()->getImmatriculationVehicule();
                        break; 
                    }
                    case 'Brand':
                    {
                        $responseTemp = $affectation->getVehicule()->getModeleVehicule()->getMarqueModeleVehicule(). ' - '.$affectation->getVehicule()->getModeleVehicule()->getNomModeleVehicule();
                        break; 
                    }
                    case 'Round':
                    {
                        $responseTemp = $affectation->getTournee()->getNumTournee().' - '.$affectation->getTournee()->getinfoTournee();
                        break; 
                    }
                    case 'Action':
                    {
                        $id = $affectation->getId();
                        $urlAffectationDetail = $this->generateUrl('logistique_affectation_details', array('id' => $id));
                        $urlAffectationModifier = $this->generateUrl('logistique_affectation_modifier', array('id' => $id));
                        $urlAffectationSupprimer = $this->generateUrl('logistique_affectation_supprimer', array('id' => $id));
                        
                        $responseTemp = "<a href=".$urlAffectationDetail." data-toggle='tooltip' data-placement='bottom' title='".$this->translator->trans('See details')."' class='btn btn-outline-dark' ><i class='fas fa-eye'></i></a> ";

                        if  ($affectation->getDateAffectation() >= new dateTime('today') )
                        {
                            $responseTemp .= "<a href='".$urlAffectationModifier."' data-toggle ='tooltip' data-placement='bottom' title='".$this->translator->trans('Edit assignment')."' class='btn btn-outline-warning' ><i class='fas fa-edit'></i></a> ";
                            $responseTemp .= "<a href='".$urlAffectationSupprimer."' data-toggle ='tooltip' data-placement='bottom' title='".$this->translator->trans('Delete assignment')."' class='btn btn-outline-danger' onclick='return confirm(`".$this->translator->trans('Are you sure you want to delete this assignment')." ?\\n".$this->translator->trans("This action is irreversible")."!`)'><i class='fas fa-trash'></i></a> ";
                        }
                        break; 
                    }
                }
                // Ajoute les données au "json"
                $response .= $responseTemp;
    
                if(++$j !== $nbColumn)
                {
                    $response .='","';
                }     
            }
            $response .= '"]';
    
            if(++$i !== $selected_objects_count)
            {
                $response .= ',';
            }
        }  
        $response .= ']}';

        return $response;
    }  
}