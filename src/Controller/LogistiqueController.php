<?php

namespace App\Controller;

use DateTime;
use ArrayObject;
use App\Entity\Colis;
use App\Entity\Requete;
use App\Entity\Tournee;
use App\Form\ColisType;
use App\Entity\Vehicule;
use App\Form\ContactType;
use App\Form\FichierType;
use App\Form\RequeteType;
use App\Form\TourneeType;
use App\Entity\Chauffeur; 
use App\Entity\CodePostal;
use App\Entity\SuiviColis;
use App\Entity\Affectation;
use App\Form\CodePostalType;
use App\Form\AffectationType;
use App\Service\Upload\Fichier;
use App\Form\AffectationEditType;
use App\Repository\EtatRepository;
use App\Form\AffectationTourneeType;
use App\Form\AffectationVehiculeType;
use App\Repository\RequeteRepository;
use App\Repository\TourneeRepository;
use App\Service\Notification\Contact;
use App\Form\AffectationChauffeurType;
use App\Repository\VehiculeRepository;
use App\Repository\ChauffeurRepository;
use App\Repository\CodePostalRepository;
use App\Repository\SuiviColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AffectationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Notification\ContactNotification;
use App\Service\Logistique\LogistiqueColisService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Logistique\LogistiqueVehiculeService;
use App\Service\Logistique\LogistiqueChauffeurService;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Logistique\LogistiqueAffectationService;
use App\Service\Logistique\LogistiqueStatistiqueService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class LogistiqueController extends AbstractController
{
    private $translator;
    private $session;
    private $logistiqueStatistiqueService;
    private $logistiqueChauffeurService;
    private $logistiqueVehiculeService; 

    /**
     * Constructeur LogistiqueController extends AbstractController
     *
     * @param TranslatorInterface $translator
     * @param SessionInterface $session
     * @param LogistiqueStatistiqueService $logistiqueStatistiqueService
     * @param LogistiqueChauffeurService $logistiqueChauffeurService
     * @param LogistiqueVehiculeService $logistiqueVehiculeService
     */
    public function __construct(TranslatorInterface $translator, SessionInterface $session, LogistiqueStatistiqueService $logistiqueStatistiqueService, LogistiqueChauffeurService $logistiqueChauffeurService, LogistiqueVehiculeService $logistiqueVehiculeService)
    {
        $this->translator                   = $translator;
        $this->session                      = $session;
        $this->logistiqueStatistiqueService = $logistiqueStatistiqueService;
        $this->logistiqueChauffeurService   = $logistiqueChauffeurService;
        $this->logistiqueVehiculeService    = $logistiqueVehiculeService;
    }

    /**
     * Permet d'afficher le tableau de bord "logistique"
     * 
     * @Route("/logistique/dashboard", name="logistique_dashboard")
     * 
     * @return Response
     */
    public function dashboard(): Response
    {
        // Si l'utilisateur a le role "ROLE_NEW_USER" on le redirige vers la page de modification du mot de passe
        $user = $this->getUser();
        if (in_array("ROLE_NEW_USER", $user->getRoles()))
        {
            $this->addFlash(
                'danger',
                $this->translator->trans('Password has to be changed') 
            );

            return $this->redirectToRoute("logistique_password");
        } 

        return $this->render('logistique/dashboard.html.twig', [
            'logistique'        => $this->getUser(),
            'statistiques'      => $this->logistiqueStatistiqueService->getStatistique(),  
            'titre'             => $this->translator->trans('Dashboard')
        ]);
    }

    //***********************************************************************************************************************************************//
    //*********************************************************** Début: Section Tournee ************************************************************//
    //***********************************************************************************************************************************************//

    // Vérifier le else
    /**
     * Permet d'affiche la liste de tous les objets "Tournee" sous forme de tableau 
     * 
     * @Route("/logistique/tournee/liste", name="logistique_tournee_liste")
     * 
     * @param TourneeRepository $repoTournee
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function listeTournee(TourneeRepository $repoTournee, EntityManagerInterface $manager): Response
    {
        // Récupère tous les objets "Tournee"
        $tournees = $repoTournee->findAll();
        $resultat = array();

        // Pour chaques objets "Tournee"
        foreach ($tournees as $tournee )
        {
            $colisFiltre =  array();
            $codePostal = array();
            $cps = array();
            $cps = $tournee->getCodePostals();
            
            // On récupère les objets "CodePostal" de l'objet "Tournee"
            foreach ($cps as $cp)
            {
                $codePostal [] = $cp->getId();
            }
            // Si l'objet "Tournee" est lié à des objets "CodePostal"
            if(! empty($codePostal))
            {
                // Convertit le tableau  d'objets "CodePostal" en string
                $cpTournee =  implode(',', $codePostal);

                // Recherche les objets "Colis" avec les codes postaux de la tournée
                $colisTournee = $manager->createQuery(
                    "SELECT c
                    FROM App\Entity\Colis c
                    JOIN c.suiviColis s
                    JOIN s.etat e
                    WHERE c.codePostal IN ($cpTournee)
                    GROUP BY s.colis 
                    "
                )   
                ->getResult();
    
                // Pour chaque Colis du Tableau "colisTournee" on récuper le colis
                foreach ($colisTournee as $colis)
                {
                    // Pour chaque suiviColis du tableau $colis->getSuiviColis() on récupere le suiviColis
                    $tableauEtatParColis = [];
                    foreach ($colis->getSuiviColis() as $suivis)
                    {
                        // On ajoute le codeEtat dans un tableau 
                        $tableauEtatParColis[] = $suivis->getEtat()->getCodeEtat();
                    } 
                    // On verifie si l'obet "Colis" a été livré (999)
                    if (! (in_array("999", $tableauEtatParColis)))
                    {
                        $colisFiltre [] = $colis;       
                    } 
                }
                // On stock l'id de la tournee et le nombre de colis  
                $resultat [$tournee->getId()] = count($colisFiltre);
            }
            else
            {
                $resultat [$tournee->getId()] = 0;
            }
        } 

        return $this->render('logistique/tournee/liste.html.twig', [
            'resultat'  => $resultat,
            'tournees'  => $tournees,       
            'titre'     => $this->translator->trans('Rounds list')
        ]);
    }

    /**
     * Permet de modifier un objet "Tounee"
     * 
     * @Route("/logistique/tournee/{id}/modifier", name="logistique_tournee_modifier")
     * 
     * @param Tournee $tournee
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierTournee(Tournee $tournee, Request $request, EntityManagerInterface $manager): Response
    { 
        $form = $this->createForm(TourneeType::class, $tournee);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($tournee);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The round has been edited'). ' !'
            ); 
            
            return $this->redirectToRoute('logistique_tournee_details', [ 'id' => $tournee->getId()] );
        }

        return $this->render('logistique/tournee/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit round') . ' : <strong>' . $tournee->getNumTournee().'</strong>',
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'ajouter un objet "Tournee"
     * 
     * @Route("/logistique/tournee/ajouter", name="logistique_tournee_ajouter")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterTournee(Request $request, EntityManagerInterface $manager): Response
    {
        $tournee = new Tournee();
        
        $form = $this->createForm(TourneeType::class, $tournee);
        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($tournee);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The round has been added'). ' !'
            ); 
              
            return $this->redirectToRoute('logistique_tournee_details', [ 'id' => $tournee->getId()] );
        }

        return $this->render('logistique/tournee/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add a round'),
            'form' => $form->createView()   
        ]);
    }

    /**
     * Permet de supprimer un objet "Tournee"
     * 
     * @Route("/logistique/tournee/{id}/supprimer", name="logistique_tournee_supprimer")
     * 
     * @param Tournee $tournee
     * @param EntityManagerInterface $manager
     * @param Request $request
     * 
     * @return Response
     */
    public function supprimerTournee(Tournee $tournee, EntityManagerInterface $manager, Request $request): Response
    {
        $numeroTournee = $tournee->getNumTournee();
      
        foreach ($tournee->getAffectations() as $affectation)
        {
            $affectations[] = $affectation;
        }

        if (empty($affectations))
        {
            $manager->remove($tournee);
            $manager->flush();
        
            $this->addFlash(
                'success',
                $this->translator->trans('The round has been deleted').' !'
            );
        }
        else
        {
            $this->addFlash(
                'danger',
                $this->translator->trans('The round has not been deleted'). ' !<br>'.$this->translator->trans('This round still has one or more assignments')
            ); 
        }
        return $this->redirectToRoute('logistique_tournee_liste');
    }

    /**
     * Permet d'afficher un objet "Tournee"
     * 
     * @Route("/logistique/tournee/{id}", name="logistique_tournee_details")
     * 
     * @param Tournee $tournee
     * 
     * @return Response
     */
    public function detailsTournee(Tournee $tournee): Response
    {
        $date = new DateTime('today');
        $currentAffectations = [];
        # Si l'objet "Tournee" n'a pas d'affectation : message autorisant la suppression de cette objet  #}
        $affectations = array();
        
        // On récupère tous les objets "Afectation" de cette tournée 
        foreach ($tournee->getAffectations() as $affectation)
        {
            $affectations[] = $affectation;
            // On sélectionne les objets "Affectation" correspondant à la date du jour 
            if ($affectation->getDateAffectation() == $date )
            {
                $current = new Affectation();
                $current =  $affectation;
                $currentAffectations [] = clone $current ;
            }
        }
        # Si l'objet "Tournee" n'a pas d'affectation : message autorisant la suppression de cette objet  #}
        if (empty($affectations))
        {
            $this->addFlash(
                'affectationVide', 
                $this->translator->trans('The round can be deleted, it has no assigments') 
            ); 
        }

        return $this->render('logistique/tournee/details.html.twig', [
            'titre' => $this->translator->trans('Details of the round'). ' : <strong>'. $tournee->getNumTournee() . '</strong> ',
            'tournee' => $tournee,
            'currentAffectations' => $currentAffectations
        ]);
    }

    /**
     * Permet d'afficher les objets "Colis"  du jour d'un objet "Tournee"
     *
     * @Route("/logistique/tournee/{id}/colis", name="logistique_tournee_colis")
     * 
     * @param Tournee $tournee
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function colisTournee(Tournee $tournee, EntityManagerInterface $manager): Response
    {  
        $codePostal = array();
        $colisFiltre = array();
        $cps = array();
        $cps = $tournee->getCodePostals();
        
        // On récupère les objets "CodePostal" de l'objet "Tournee"
        foreach ($cps as $cp)
        {
            $codePostal [] = $cp->getId();
        }

        if (! empty($codePostal)){
            $cpTournee =  implode(',', $codePostal);

            //colis avec codes postaux de la tournée
            $colisTournee = $manager->createQuery(
                "SELECT c
                FROM App\Entity\Colis c
                JOIN c.suiviColis s
                JOIN s.etat e
                WHERE c.codePostal IN ($cpTournee)
                GROUP BY s.colis 
                "
            )   
            ->getResult();

            //Pour chaque Colis du Tableau "colisTournee" on récuper le colis
            foreach ($colisTournee as $colis)
            {
                //Pour chaque suiviColis du tableau $colis->getSuiviColis() on récupere le suiviColis
                $tableauEtatParColis = [];
                foreach ($colis->getSuiviColis() as $suivis)
                {
                    //On ajoute le codeEtat dans un tableau 
                    $tableauEtatParColis[] = $suivis->getEtat()->getCodeEtat();
                } 
                //On verifie si l'obet "Colis" a été livré (999)
                if (! (in_array("999", $tableauEtatParColis)))
                {
                    $colisFiltre [] = clone $colis;       
                } 
            }
        }

        return $this->render('logistique/tournee/colis.html.twig', [
            'idTournee' => $tournee->getId(),
            'titre' => $this->translator->trans('Round parcels list') . ' : <strong>' . $tournee->getNumTournee() .'</strong>',
            'colis' =>$colisFiltre
        ]);
    }

    /**
     * Permet d'afficher les détails d'un objet "Colis" de la tournee (expédition ou enlèvement)
     * 
     * @Route("/logistique/tournee/{idTournee}/expedition/{id}", name="logistique_tournee_expedition_details")
     * @Route("/logistique/tournee/{idTournee}/enlevement/{id}", name="logistique_tournee_enlevement_details")
     *
     * @param Colis $colis
     * @param string $idTournee
     * @param SuiviColisRepository $repoSuiviColis
     * 
     * @return Response
     */
    public function detailsColisTournee(Colis $colis, $idTournee, SuiviColisRepository $repoSuiviColis): Response
    {
        $suiviColis = $repoSuiviColis->findBy(['colis' =>  $colis], ['dateSuiviColis' =>'DESC'] );

        return $this->render('logistique/tournee/colis-details.html.twig', [
            'idTournee' => $idTournee,
            'titre' => $colis->getTypeColis() ? $this->translator->trans('Details of the delivery').' : <strong>' .$colis->getNumeroColis().'</strong>' : $this->translator->trans('Details of the pickup').' : <strong>' .$colis->getNumeroColis().'</strong>',
            'colis' => $colis ,
            'suiviColis' => $suiviColis
        ]);
    }

    /**
     * Permet d'afficher les objets "Affectation" du jour et future d'un objet "Tournee" 
     * 
     * @Route("/logistique/tournee/{id}/affectation", name="logistique_tournee_affectation")
     * 
     * @param Tournee $tournee
     * 
     * @return Response
     */
    public function affectationTournee(Tournee $tournee): Response
    {
       $affectationNonFlitre = $tournee->getAffectations();
       $affectationFiltre = array();
       foreach ($affectationNonFlitre as $affectation)
       {
            if ( $affectation->getDateAffectation() >= new dateTime('today') )
            {
                $affectationFiltre [] = $affectation;
            }
       }

        return $this->render('logistique/tournee/affectation.html.twig', [
            'titre' => $this->translator->trans('Round assignments') . ' : <strong>' . $tournee->getNumTournee().'</strong>',
            'affectations' => $affectationFiltre 
        ]);
    }
   
    //***********************************************************************************************************************************************//
    //********************************************************** Fin: Section Tournee ***************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //******************************************************* Début: Section Affectation ************************************************************//
    //***********************************************************************************************************************************************//
    
    /**
     * Retourne un fichier json à la requête Ajax de dataTAbles Affectation
     *
     * @Route("/logistique/affectation/liste/api/{route}", name="logistique_affectation_liste_api")
     * 
     * @param string $route
     * @param LogistiqueAffectationService $logistiqueAffectationService
     * 
     * @return JsonResponse
     */
    public function listeAffectationApi($route, LogistiqueAffectationService $logistiqueAffectationService): JsonResponse
    {  
        $response = $logistiqueAffectationService->dataTablesAffectationApi($route) ;
        
        $returnResponse = new JsonResponse();
        $returnResponse->setJson($response);

        return $returnResponse;
    }

    /**
     * Permet d'affiche la liste de toutes les objets "Affectation" futures sous forme de tableau 
     * 
     * @Route("/logistique/affectation/liste", name="logistique_affectation_liste")
     * 
     * @return Response
     */
    public function listeAffectationFuture(): Response
    {   
        return $this->render('logistique/affectation/liste.html.twig', [          
            'titre' => $this->translator->trans('Assignments list'),   
        ]);
    }

    /**
     * Permet d'affiche la liste de toutes les objets "Affectation" passées sous forme de tableau 
     * 
     * @Route("/logistique/affectation/archive", name="logistique_affectation_archive")
     * 
     * @return Response
     */
    public function listAffectationArchive(): Response
    { 
        return $this->render('logistique/affectation/liste.html.twig', [        
            'titre' => $this->translator->trans('Assignment archives list'),
        ]);
    }

    /**
     * Permet d'affiche la liste de tous les objets "Affectation" invalides
     * 
     * @Route("/logistique/affectation/liste/invalide", name="logistique_affectation_liste_invalide")
     * 
     * @param AffectationRepository $repoAffectation
     * 
     * @return Response
     */
    public function listeAffectationinvalide(AffectationRepository $repoAffectation): Response
    {
        //on récupère les idAffectation stockées dans la session 
        $tableauIdAffectation = $this->session->get('idAffectation');
        $affectationsInvalides = array();
        
        //pour chaques id du tableau
        foreach ( $tableauIdAffectation as $id )
        {
            //On recherche les affectations correspondantes aux IdAffectation
            $affectation = $repoAffectation->find($id);
            //On stocke l'affectation dans un tableau qui sera envoyé à la vue "affectation invalide"
            $affectationsInvalides [] = clone $affectation;
        }

        return $this->render('logistique/affectation/invalide.html.twig', [
            'affectationsAbandonnees' =>  $affectationsInvalides,
            'chauffeur' => $this->session->get('chauffeur'), 
            'vehicule' =>  $this->session->get('vehicule'),
            'tournee' => $this->session->get('tournee'), 
            'titre' => $this->translator->trans('Invalid Assignments list')
        ]);
    }

    /**
     * Retourne un fichier Json avec tous les chauffeurs ayant le permis adéquat en fonction du véhicule
     * 
     * @Route("/logistique/affectation/ajouter-modifier/api/", name="logistique_affectation_ajouter-modifier_api")
     *  
     * @param Request $request
     * @param LogistiqueChauffeurService $logistiqueChauffeurService
     * 
     * 
     * @return JsonResponse
     */
    public function ajouterModifierAffectationApi(Request $request, LogistiqueChauffeurService $logistiqueChauffeurService): JsonResponse
    {
        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) 
        {
            $chauffeurValides = [];
           
            $chauffeurs = $logistiqueChauffeurService->getChauffeurByCategoriePermisConduire($request->request->get('idVehicule')) ;
            
            
            foreach ($chauffeurs as $chauffeur)
            {
                $tableauCategories = array();
                foreach ($chauffeur->getPermisConduire()->getCategoriePermisConduires() as $categorie)
                {
                    $tableauCategories[] =  $categorie->getNomCategoriePermisConduire();
                }
                if (empty($tableauCategories)) $tableauCategories = 'No licence';

                $chauffeurValides[] = ['id' => $chauffeur->getId(), 'nom' => $chauffeur->getNomChauffeur(), 'prenom' => $chauffeur->getPrenomChauffeur() , 'categories' => implode(', ', $tableauCategories)];
            }

            return $this->json(['chauffeurs' => $chauffeurValides], 200, []); 
        }   
    }

    /**
     * Permet de modifier un objet "Affectation"
     * 
     * @Route("/logistique/affectation/{id}/modifier", name="logistique_affectation_modifier")
     * 
     * @param Affectation $affectation
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param LogistiqueAffectationService $logistiqueAffectationService 
     *
     * @return Response
     */
    public function modifierAffectation(Affectation $affectation, Request $request, EntityManagerInterface $manager, LogistiqueAffectationService $logistiqueAffectationService): Response
    {
        // On empèche la modification d'un objet "Affectation" passé
        if ( $affectation->getDateAffectation() < new dateTime('today'))
        {
            $this->addFlash(
                'danger', 
                $this->translator->trans('Past assingments can\'t be edites')
            );  

            return $this->redirectToRoute('logistique_affectation_details', [ 'id' => $affectation->getId()] );
        }
      
        $oldAffectation = clone $affectation;
        $form = $this->createForm(AffectationEditType::class, $affectation);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {    
            $erreurAffectation = $logistiqueAffectationService->affectationModifierControle($oldAffectation, $affectation);
            
            // Si erreurAffectation est false (pas d'erreur)
            if (! $erreurAffectation)
            {
                // On persiste la modification
                $manager->persist($affectation);
                $manager->flush();         
            
                $this->addFlash(
                    'success', 
                    $this->translator->trans('The assignment has been edited').' !'
                ); 
                return $this->redirectToRoute('logistique_affectation_details', [ 'id' => $affectation->getId()] );
            } 
            else 
            {
                $this->addFlash(
                    'warning', 
                    $this->translator->trans('Please, check entered informations')
                 ); 
            }
        }

        return $this->render('logistique/affectation/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit assignment') . ' : <strong>' . $affectation->getDateAffectation()->format('d.m.Y') .'</strong>',
            'form' => $form->createView(),
            'date' => $affectation->getDateAffectation()
        ]);
    }

    /**
     * Permet d'ajouter un ou plusieurs objets "Affectation" selon l'entité (chauffeur, vehicule, tournee)
     * 
     * @Route("/logistique/affectation/ajouter", name="logistique_affectation_ajouter")
     * @Route("/logistique/affectation/ajouter/chauffeur/{entite}", name="logistique_affectation_chauffeur_ajouter")
     * @Route("/logistique/affectation/ajouter/vehicule/{entite}", name="logistique_affectation_vehicule_ajouter")
     * @Route("/logistique/affectation/ajouter/tournee/{entite}", name="logistique_affectation_tournee_ajouter")
     * 
     * @param string $entite|null
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ChauffeurRepository $repoChauffeur
     * @param VehiculeRepository $repoVehicule
     * @param TourneeRepository $repoTournee
     * @param LogistiqueAffectationService $logistiqueAffectationService
     * 
     * @return Response
     */
    public function ajouterAffectation($entite = null, Request $request, EntityManagerInterface $manager, ChauffeurRepository $repoChauffeur, VehiculeRepository $repoVehicule, TourneeRepository $repoTournee, LogistiqueAffectationService $logistiqueAffectationService): Response
    {
        $affectation = new Affectation();
        
        $titre = $this->translator->trans('Add an assignment');

        if ($request->attributes->get('_route') == "logistique_affectation_ajouter")  
        {
            $route = 'logistique/affectation/ajouter-modifier.html.twig';
            $form = $this->createForm(AffectationType::class, $affectation);
        }
        else if ($request->attributes->get('_route') == "logistique_affectation_chauffeur_ajouter") 
        {
            $chauffeur = $repoChauffeur->find($entite);
            // On récupère le tableau d'objet  "CategoriePermisConduire" de l'objet "PermisConduire" de l'objet "Chauffeur"
            $categories = $chauffeur->getPermisConduire()->getCategoriePermisConduires();
            // On fait appel au service "logistiqueChauffeurService" pour déterminer la 'mma' selon la catégorie de permis du chauffeur
            $mma = $this->logistiqueChauffeurService->getMma($categories);
            $affectation->setChauffeur($chauffeur); 
            // On récupère toutes les catégories du permis du chauffeur pour l'afficher dans le titre
            $tableauCategories = array();
            foreach ($chauffeur->getPermisConduire()->getCategoriePermisConduires() as $categorie)
            {
                $tableauCategories[] =  $categorie->getNomCategoriePermisConduire();
            }
            if (empty($tableauCategories)) $tableauCategories = 'No licence';

            $titre .= ' : <strong>'. $chauffeur->getNomChauffeur().' '.$chauffeur->getPrenomChauffeur() .'</strong>'.' ('.implode(', ', $tableauCategories).')';
            $route = 'logistique/affectation/chauffeur/ajouter.html.twig';
            // On envoie en parametre "$mma" au formulaire pour le query_builder du chanmp de type "Select"
            $form = $this->createForm(AffectationChauffeurType::class, $affectation, ['mma' => $mma]);
        }
        else if ($request->attributes->get('_route') == "logistique_affectation_vehicule_ajouter") 
        {   
            $vehicule = $repoVehicule->find($entite);
            // On récupère la "mma" du vehicule
            $mma = $vehicule->getModeleVehicule()->getCapaciteModeleVehicule();
            // On fait appel au service "logistiqueVehiculeService" pour déterminer la catégorie de permis nécéssaire au véhicule
            $categoriePermis = $this->logistiqueVehiculeService->getCategoriePermis($mma); 
            $affectation->setVehicule($vehicule);
            $titre .= ' : <strong>'. $vehicule->getImmatriculationVehicule().'</strong>';
            $route = 'logistique/affectation/vehicule/ajouter.html.twig';
            // On envoie en parametre "$permisCategorie" au formulaire pour le query_builder du chanmp de type "Select"
            $form = $this->createForm(AffectationVehiculeType::class, $affectation, ['categoriePermis' => $categoriePermis]);  
        }
        else if ($request->attributes->get('_route') == "logistique_affectation_tournee_ajouter") 
        {
            $tournee = $repoTournee->find($entite);
            $affectation->setTournee($tournee);
            $titre .= ' : <strong>'. $tournee->getNumTournee().'</strong>';
            $route = 'logistique/affectation/tournee/ajouter.html.twig';
            $form = $this->createForm(AffectationTourneeType::class, $affectation);
        }
        else 
        {
            return $this->redirect($request->headers->get('referer'));
        }

        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        {   
            // Si la date de fin n'est pas définie on persistera une affectation : Affectation unique
            if ($form->get('dateFin')->getData() == "")
            {
                //Renvoi true si il y a une erreur
                $erreurAffectation = $logistiqueAffectationService->affectationUniqueControle($affectation);   
               
                // Si erreurAffectation est false (pas d'erreur)
                if (! $erreurAffectation)
                {
                    // On persiste une affectation
                    $manager->persist($affectation);
                    $manager->flush();         
                
                    $this->addFlash(
                        'success', 
                        $this->translator->trans('The unique assignment has been added'). ' !'
                    ); 

                    return $this->redirectToRoute('logistique_affectation_details', [ 'id' => $affectation->getId()] );
                } 
                else 
                {
                    $this->addFlash(
                        'warning', 
                        $this->translator->trans('Please, check entered informations')
                     ); 
                }
            }

            // Si la date de fin est défini, on persiste toutes les dates : Affectation multiple
            else
            {      
                // On récupère les dates de début et de fin du formulaire        
                $dateDebut = clone $form->get('dateAffectation')->getData();
                $dateFin = clone $form->get('dateFin')->getData();
                
                // Retourne true s'il y a une erreur
                $erreurAffectation = $logistiqueAffectationService->affectationMiltiplePrecontrole($affectation, $dateDebut, $dateFin);

                if ($erreurAffectation)
                {  
                    $this->addFlash(
                       'warning', 
                       $this->translator->trans('Please, check entered informations')
                    ); 

                    return $this->render('logistique/affectation/ajouter-modifier.html.twig', [
                        'titre' => $this->translator->trans('Add an assignment'),
                        'form' => $form->createView()   
                    ]);                        
                }  
                
                // Retourne une tableau d'objets "Affectation" valide et un tableau d'ID d'affectation invalide
                $resultat =  $erreurAffectation = $logistiqueAffectationService->affectationMultipleControle($affectation, $dateDebut, $dateFin);

                return $this->render('logistique/affectation/multiple.html.twig', [
                    'titre' => $this->translator->trans('Multiple assignments list'),
                    'affectations' => $resultat['tabAffectations'],
                    'affectationsAbandonnees' => $resultat['listeId'],
                ]);               
            }
        }

        return $this->render($route, [
            'titre' => $titre,
            'form' => $form->createView()   
        ]);
    }

    /**
     * Permet de supprimer un objet "Affectation"
     * 
     * @Route("/logistique/affectation/{id}/supprimer", name="logistique_affectation_supprimer")
     * 
     * @param Affectation $affectation
     * @param EntityManagerInterface $manager
     * @param Request $request
     * 
     * @return Response
     */
    public function supprimerAffectation(Affectation $affectation, EntityManagerInterface $manager, Request $request): Response
    {
        // On empêche la suppression d'un objet "Affectation" passé
        if ( $affectation->getDateAffectation() < new dateTime('today'))
        {
            $this->addFlash(
                'danger', 
                $this->translator->trans('Past assingments can\'t be deleted')
            );  

            return $this->redirectToRoute('logistique_affectation_details', [ 'id' => $affectation->getId()] );
        }

        $manager->remove($affectation);
        $manager->flush();
       
        $this->addFlash(
            'success',
            $this->translator->trans('The assignment has been deleted').' !'
        );
        
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Permet d'afficher un objet "Affectation"
     * 
     * @Route("/logistique/affectation/{id}", name="logistique_affectation_details")
     * 
     * @param Affectation $affectation
     * @param AffectationRepository $repoAffectation
     * 
     * @return Response
     */
    public function detailsAffectation(Affectation $affectation, AffectationRepository $repoAffectation): Response
    {
        $tournee = $affectation->getTournee();
        $affectations = $repoAffectation->findBy([ 'dateAffectation' => $affectation->getDateAffectation() ,'tournee' => $tournee ]);
        $extra = array();
    
        foreach ( $affectations as $aff )
        {
            if ($aff->getChauffeur()->getId() != $affectation->getChauffeur()->getId())
            {
                $extra[] = clone $aff;
            }
        }

        return $this->render('logistique/affectation/details.html.twig', [
            'titre' => $this->translator->trans('Details of the assignment').' : <strong>' . $affectation->getDateAffectation()->format('d.m.Y').'</strong>',
            'affectation' => $affectation, 
            'extra' =>  $extra   
        ]);
    }

    //***********************************************************************************************************************************************//
    //******************************************************** Fin: Section Affectation *************************************************************//
    //***********************************************************************************************************************************************//
    
    //***********************************************************************************************************************************************//   

    //***********************************************************************************************************************************************//
    //******************************************************* Début: Section Chauffeur **************************************************************//
    //***********************************************************************************************************************************************//
    
    /**
     * Permet d'affiche la liste de tous les objets "Chauffeur" actifs sous forme de tableau
     * 
     * @Route("/logistique/chauffeur/liste", name="logistique_chauffeur_liste")
     * 
     * @param ChauffeurRepository $repoChauffeur
     * @param AffectationRepository $repoAffectation
     * 
     * @return Response
     */
    public function listeChauffeur(ChauffeurRepository $repoChauffeur, AffectationRepository $repoAffectation): Response
    {   
        $tableau = array();
        $affectationFuture = array();
        $chauffeurs = $repoChauffeur->findBy(['statutChauffeur' => 1], []);

        // Pour chaques chauffeurs on récupère les affectations futures dans '$affetcationFuture'
        foreach ($chauffeurs as $chauffeur)
        {
            $affetcationFuture = $repoAffectation->findByDateFuturChauffeur($chauffeur);
            
            // on envoie à la vue un tableau $chauffeur/$affectationFuture
            $tableau[] = ['chauffeur' => $chauffeur, 'affectations' => $affetcationFuture];
        }

        return $this->render('logistique/chauffeur/liste.html.twig', [
            'tableau' => $tableau,  
            'titre' => $this->translator->trans('Drivers list') 
        ]);
    }

    /**
     * Permet d'affiche la liste de tous les objets "Chauffeur" actifs et sans affectaion pour la journée en cours sous forme de tableau
     * 
     * @Route("/logistique/chauffeur/list/libre", name="logistique_chauffeur_libre")
     * 
     * @param ChauffeurRepository $repoChauffeur
     * @param AffectationRepository $repoAffectation
     * 
     * @return Response
     */
    public function listeChauffeurLibre(ChauffeurRepository $repoChauffeur, AffectationRepository $repoAffectation): Response
    {   
        $tableau = array();
        $affectationFuture = array();
        $chauffeurLibre = $this->logistiqueChauffeurService->getChauffeurLibre();

        // Pour chaques chauffeurs on récupère les affectations futures dans '$affetcationFuture'
        foreach ($chauffeurLibre as $chauffeur)
        {
            $affetcationFuture = $repoAffectation->findByDateFuturChauffeur($chauffeur);
            
            // on envoie à la vue un tableau $chauffeur/$affectationFuture
            $tableau[] = ['chauffeur' => $chauffeur, 'affectations' => $affetcationFuture];
        }

        return $this->render('logistique/chauffeur/liste.html.twig', [
            'tableau' => $tableau,   
            'titre' => $this->translator->trans('Free drivers list') 
        ]);
    }

    /**
     * Permet d'afficher les objets "Affectation" d'un objet "Chauffeur" à partir du jour en cours
     * 
     * @Route("/logistique/chauffeur/{id}/affectation", name="logistique_chauffeur_affectation")
     * 
     * @param Chauffeur $chauffeur
     * 
     * @return Response
     */
    public function affectationChauffeur(Chauffeur $chauffeur): Response
    {
        $affectations =  array();

        foreach($chauffeur->getAffectations() as $affectation) 
        {
            if( $affectation->getDateAffectation() >= new \DateTime('today'))
            {
                $affectations [] = clone $affectation;
            }   
        }

        return $this->render('logistique/chauffeur/affectation.html.twig', [
            'titre' => $this->translator->trans('Driver assignments') . ' :  <strong>' . $chauffeur->getNomChauffeur() . " " . $chauffeur->getPrenomChauffeur().'</strong>',
            'affectations' => $affectations 
        ]);
    }
    
    //***********************************************************************************************************************************************//
    //******************************************************** Fin: Section chauffeur ***************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//   

    //***********************************************************************************************************************************************//
    //******************************************************* Début: Section Vehicule ***************************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'affiche la liste de tous les objets "Vehicule" actifs sous forme de tableau
     * 
     * @Route("/logistique/vehicule/liste", name="logistique_vehicule_liste")
     * 
     * @param VehiculeRepository $repoVehicule
     * @param AffectationRepository $repoAffectation
     * 
     * @return Response
     */
    public function listeVehicule(VehiculeRepository $repoVehicule, AffectationRepository $repoAffectation): Response
    {   
        $tableau = array();
        $affectationFuture = array();
        $vehicules = $repoVehicule->findBy(['statutVehicule' => 1], []);

        // Pour chaques vehicules on récupère les affectations futures dans '$affetcationFuture'
        foreach ($vehicules as $vehicule)
        {
            $affetcationFuture = $repoAffectation->findByDateFuturVehicule($vehicule);
            // on envoie à la vue un tableau $vehicule/$affectationFuture
            $tableau[] = ['vehicule' => $vehicule, 'affectations' => $affetcationFuture];
        }

        return $this->render('logistique/vehicule/liste.html.twig', [
            'tableau' => $tableau,  
            'titre' => $this->translator->trans('Vehicles list') 
        ]);
    }

    /**
     * Permet d'affiche la liste de tous les objets "Vehicule" actifs et sans affectaion pour la journée en cours sous forme de tableau
     * 
     * @Route("/logistique/vehicule/list/libre", name="logistique_vehicule_libre")
     * 
     * @param AffectationRepository $repoAffectation
     * 
     * @return Response
     */
    public function listeVehiculeLibre(AffectationRepository $repoAffectation): Response
    {   
        $tableau = array();
        $affectationFuture = array();
        $vehiculeLibre = $this->logistiqueVehiculeService->getVehiculeLibre();

        // Pour chaques vehicules on récupère les affectations futures dans '$affetcationFuture'
        foreach ($vehiculeLibre as $vehicule)
        {
            $affetcationFuture = $repoAffectation->findByDateFuturVehicule($vehicule);
            // on envoie à la vue un tableau $vehicule/$affectationFuture
            $tableau[] = ['vehicule' => $vehicule, 'affectations' => $affetcationFuture];
        }

        return $this->render('logistique/vehicule/liste.html.twig', [
            'tableau' => $tableau,  
            'titre' => $this->translator->trans('Free vehicles list') 
        ]);
    }

    /**
     * Permet d'afficher les objets "Affectation" d'un objet "vehicule" à partir du jour en cours
     * 
     * @Route("/logistique/vehicule/{id}/affectation", name="logistique_vehicule_affectation")
     * 
     * @param Vehicule $vehicule
     * 
     * @return Response
     */
    public function affectationVehicule(Vehicule $vehicule): Response
    {
        $affectations =  array();

        foreach($vehicule->getAffectations() as $affectation) 
        {
            if( $affectation->getDateAffectation() >= new DateTime('today'))
            {
                $affectations [] = clone $affectation;
            }   
        }
 
        return $this->render('logistique/vehicule/affectation.html.twig', [
            'titre' => $this->translator->trans('Vehicle assignments') . ' :  <strong>' . $vehicule->getImmatriculationVehicule().'</strong>',
            'affectations' => $affectations 
        ]);
    }

    //***********************************************************************************************************************************************//
    //********************************************************* Fin: Section Vehicule ***************************************************************//
    //***********************************************************************************************************************************************//
    
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //******************************************************* Début: Section Colis ******************************************************************//
    //***********************************************************************************************************************************************//
     
    /**
     * Retiurne un fichier json à la requête Ajax de dataTAbles Colis
     *
     * @Route("/logistique/colis/api/{typeColis}/{route}", name="logistique_colis_api")
     * 
     * @param bool $typeColis
     * @param string $route
     * @param LogistiqueColisService $logistiqueColisService
     * 
     * @return JsonResponse
     */
    public function colisApi($typeColis, $route, LogistiqueColisService $logistiqueColisService): JsonResponse
    {  
        $response = $logistiqueColisService->dataTablesColisApi($typeColis, $route);
        $returnResponse = new JsonResponse();
        $returnResponse->setJson($response);

        return $returnResponse; 
    }
    
    /**
     * Permet d'afficher la liste de tous les objets "Colis", expédition ou enlèvement
     * 
     * @Route("/logistique/expedition/liste/{typeColis}", name="logistique_expedition_liste")
     * @Route("/logistique/enlevement/liste/{typeColis}", name="logistique_enlevement_liste")
     * 
     * @param bool $typeColis
     * 
     * @return Response
     */
    public function listeColis($typeColis): Response 
    {  
        return $this->render('logistique/colis/liste.html.twig', [
            'titre' => $typeColis ? $this->translator->trans('Deliveries list') : $this->translator->trans('Pickup list'),
            'typeColis' => $typeColis,
        ]);
    }  

    /**
     * Permet d'afficher la liste de tous les objets "Colis" pris en charge le jour en cours (expédition et enlèvement)
     * 
     * @Route("/logistique/expedition/nouvelle/{typeColis}", name="logistique_expedition_nouvelle")
     * @Route("/logistique/enlevement/nouvelle/{typeColis}", name="logistique_enlevement_nouvelle")
     * 
     * @param bool $typeColis
     * 
     * @return Response
     */
    public function listeColisDuJour($typeColis): Response 
    {
        return $this->render('logistique/colis/liste.html.twig', [   
            'titre' => $typeColis ? $this->translator->trans('Today deliveries list') : $this->translator->trans('Today pickup list'),
            'typeColis' => $typeColis 
        ]); 
    }

    /**
     * Permet d'afficher la liste de tous les objets "Colis" ouverts (expéditions et enlèvements)
     * 
     * @Route("/logistique/expedition/en-cours/{typeColis}", name="logistique_expedition_en-cours")
     * @Route("/logistique/enlevement/en-cours/{typeColis}", name="logistique_enlevement_en-cours")
     * 
     * @param bool $typeColis
     * 
     * @return Response
     */
    public function listeColisEnCours($typeColis): Response 
    { 
        return $this->render('logistique/colis/liste.html.twig', [
            'titre' => $typeColis ? $this->translator->trans('Opened deliveries list') : $this->translator->trans('Opened pickup list'),
            'typeColis' => $typeColis,
        ]);   
    }
    
    /**
     * Permet d'afficher la liste de tous les objets "Colis"  livrés
     * 
     * @Route("/logistique/expedition/livre/{typeColis}", name="logistique_expedition_livre")
     * @Route("/logistique/enlevement/livre/{typeColis}", name="logistique_enlevement_livre")
     * 
     * @param bool $typeColis
     * 
     * @return Response
     */
    public function listeColisFerme($typeColis): Response 
    { 
        return $this->render('logistique/colis/liste.html.twig', [
            'titre' => $typeColis ? $this->translator->trans('Closed deliveries list') : $this->translator->trans('Closed pickup list'),
            'typeColis' => $typeColis,
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "Colis" en litige
     * 
     * @Route("/logistique/expedition/litige/{typeColis}", name="logistique_expedition_litige")
     * @Route("/logistique/enlevement/litige/{typeColis}", name="logistique_enlevement_litige")
     * 
     * @param bool $typeColis
     * 
     * @return Response
     */
    public function listeColisLitige($typeColis): Response 
    { 
        return $this->render('logistique/colis/liste.html.twig', [
            'titre' => $typeColis ? $this->translator->trans('Issues deliveries list') : $this->translator->trans('Issues pickup list'),
            'typeColis' => $typeColis,
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "Colis" express
     * 
     * @Route("/logistique/express", name="logistique_express")
     *
     * @return Response
     */
    public function listeColisExpressDuJour(): Response 
    { 
        return $this->render('logistique/colis/liste.html.twig', [
            'titre' => $this->translator->trans('Express deliveries list'),
            'typeColis' => 1,
        ]);
    }

    //************************************************** Début : Section fichier liste Colis ********************************************************//
  
    /**
     * Permet d'uploader une liste de colis (expédition ou enlèvement) à charger en DB 
     * 
     * @Route("/logistique/expedition/ajouter/{typeColis}", name="logistique_expedition_ajouter")
     * @Route("/logistique/enlevement/ajouter/{typeColis}", name="logistique_enlevement_ajouter")
     * 
     * @param bool $typeColis
     * @param Request $request
     * @param LogistiqueColisService $logistiqueColisService
     *
     * @return Response
     */
    public function AjouterColis($typeColis, Request $request, LogistiqueColisService $logistiqueColisService): Response
    {
        $fichier = new Fichier();
        $tableauErreurs = [];
        
        $form = $this->createForm(FichierType::class, $fichier);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $uploadedFile = $form->get('fichier')->getData();
            // En fonction du nombre de ligne du fichier csv on mofifie le le paramètre time_limit() du serveur php (php.ini) 
            if ($uploadedFile) 
            {  
                $lignes = count(file($uploadedFile));
                
                // Au delà de 15000 ligne le fichier est refusé 
                if ($lignes >= 15000)
                {
                    $this->addFlash(
                        'warning',
                        $this->translator->trans('File contains more than 15000 entries').' !'
                    );   
                }
                else if ($lignes >= 10000)
                {
                    set_time_limit(480);
                    $tableauErreurs =  $logistiqueColisService->uploadListColis($uploadedFile, $typeColis);
                    set_time_limit(120);
                }
                else
                {
                    set_time_limit(240);
                    $tableauErreurs =  $logistiqueColisService->uploadListColis($uploadedFile, $typeColis);
                    set_time_limit(120);
                }
            }    
        }

        return $this->render('logistique/colis/ajouter.html.twig', [
            'titre' => $typeColis ? $this->translator->trans('Upload deliveries list') : $this->translator->trans('Upload pickup list'),
            'form' => $form->createView(),
            'tableauErreurs' => $tableauErreurs,
        ]);
    }   
      
    //**************************************************** Fin : Section fichier liste Colis ********************************************************//

    /**
     * Permet d'afficher les détails d'un objet "Colis" (expédition ou enlèvement)
     * 
     * @Route("/logistique/expedition/{id}", name="logistique_expedition_details")
     * @Route("/logistique/enlevement/{id}", name="logistique_enlevement_details")
     * 
     * @param Colis $colis
     * @param SuiviColisRepository $repoSuiviColis
     * 
     * @return Response
     */
    public function detailsColis(Colis $colis, SuiviColisRepository $repoSuiviColis): Response
    {
        $suiviColis = $repoSuiviColis->findBy(['colis' =>  $colis], ['dateSuiviColis' =>'DESC'] );
       
        return $this->render('logistique/colis/details.html.twig', [
            'titre' => $colis->getTypeColis() ? $this->translator->trans('Details of the delivery').' : <strong>' .$colis->getNumeroColis().'</strong>' : $this->translator->trans('Details of the pickup').' : <strong>' .$colis->getNumeroColis().'</strong>',
            'colis' => $colis ,
            'suiviColis' => $suiviColis
        ]);
    }
    
    /**
     * Permet de modifier un objet "Colis" ( la propriété "noteColis" uniquement) (expédition et enlèvement)
     * 
     * @Route("/logistique/expedition/{id}/modifier", name="logistique_expedition_modifier")
     * @Route("/logistique/enlevement/{id}/modifier", name="logistique_enlevement_modifier")
     * 
     * @param Colis $colis
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierColis(Colis $colis, Request $request, EntityManagerInterface $manager): Response
    { 
        $anciensMessage = $colis->getNoteColis();
        $colis->setNoteColis('');
       
        $form = $this->createForm(ColisType::class, $colis); 
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user = $this->getUser();
            $nouveMessage = $colis->getNoteColis();
            $colis->setNoteColis($anciensMessage.'<br> - '  .$user->getNomLogistique().' '.$user->getPrenomLogistique().' ('.date('d.m.Y H:m').').<br> Message : <strong>'.$nouveMessage.'</strong>'  );

            $manager->persist($colis);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The parcel has been edited'). ' !'
            ); 

            if ($colis->getTypeColis())
            {
                return $this->redirectToRoute('logistique_expedition_details', [ 'id' => $colis->getId()] );
            }
            else
            {
                return $this->redirectToRoute('logistique_enlevement_details', [ 'id' => $colis->getId()] );
            }     
        }

        return $this->render('logistique/colis/modifier.html.twig', [
            'titre' => $colis->getTypeColis() ? $this->translator->trans('Add delivery comment').' : <strong>' .$colis->getNumeroColis().'</strong>' : $this->translator->trans('Add pickup comment').' : <strong>' .$colis->getNumeroColis(). '</strong>',
            'form' => $form->createView()
        ]);
    }

    //***********************************************************************************************************************************************//
    //******************************************************** Fin: Section Colis *******************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//   

    //***********************************************************************************************************************************************//
    //******************************************************* Début: Section CodePostal *************************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'afficher la liste de tous les objets "CodePostal"
     * 
     * @Route("/logistique/code-postal/liste", name="logistique_code-postal_liste")
     * 
     * @param CodePostalRepository $repoCodePostal
     * 
     * @return Response
     */
    public function listeCodePostal(CodePostalRepository $repoCodePostal): Response
    {
        $codePostals = $repoCodePostal->findAll();
 
        return $this->render('logistique/code-postal/liste.html.twig', [
            'titre' => $this->translator->trans('Postal codes list'),
            'codePostals' => $codePostals 
        ]);
    }

    /**
     * Permet de modifier un objet "CodePostal"
     * 
     * @Route("/logistique/code-postal/{id}/modifier", name="logistique_code-postal_modifier")
     * 
     * @param CodePostal $codePostal
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierCodePostal(CodePostal $codePostal, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(CodePostalType::class, $codePostal);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($codePostal);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The postal code has been edited').' !'
            ); 
            
            return $this->redirectToRoute('logistique_code-postal_liste');
        }

        return $this->render('logistique/code-postal/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit postal code') . ' : <strong>' . $codePostal->getnumCodePostal().'</strong>',
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'ajouter un objet "CodePostal"
     * 
     * @Route("/logistique/code-postal/ajouter", name="logistique_code-postal_ajouter")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterCodePostal(Request $request, EntityManagerInterface $manager): Response
    {
        $codePostal = new CodePostal();
        
        $form = $this->createForm(CodePostalType::class, $codePostal);
        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        { 
            $manager->persist($codePostal);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The postal code has been added'). ' !'
            ); 
              
            return $this->redirectToRoute('logistique_code-postal_liste');
        }

        return $this->render('logistique/code-postal/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add a postal code'),
            'form' => $form->createView() 
        ]);
    }

    /**
     * Permet de supprimer un objet "CodePostal"
     * 
     * @Route("/logistique/code-postal/{id}/supprimer", name="logistique_code-postal_supprimer")
     * 
     * @param CodePostal $codePostal
     * @param EntityManagerInterface $manager
     * @param Request $request
     * 
     * @return Response
     */
    public function supprimerCodePostal(CodePostal $codePostal, EntityManagerInterface $manager, Request $request): Response
    {
        $numeroCodePostal = $codePostal->getId();
        $manager->remove($codePostal);
        $manager->flush();
       
        $this->addFlash(
            'success',
            $this->translator->trans('The postal code has been deleted').' !'
        );
        
        return $this->redirect($request->headers->get('referer'));
    }

    //***********************************************************************************************************************************************//
    //******************************************************** Fin: Section CodePostal **************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //******************************************************* Début: Section SuiviColis *************************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet de cloturer manuellement un objet "Colis" comme étant livrée
     * 
     * @Route("/logistique/colis/{id}/cloturer", name="logistique_colis_cloturer")
     * 
     * @param Colis $colis
     * @param EntityManagerInterface $manager
     * @param EtatRepository $repoEtat
     * @param Request $request
     * 
     * @return Response
     */
    public function ajouterSuiviColis999(Colis $colis, EntityManagerInterface $manager, EtatRepository $repoEtat, Request $request): Response 
    {
        //on récupère l'objet "Etat" correspondant à l'attribut $codeEtat "999" (colis livré)
        $etat = $repoEtat->findOneBy(['codeEtat' => 999]);
        $tableauEtat = array(); 
        $tableauSuiviColis = $colis->getSuiviColis();
        $cloturer = false;

        foreach ($tableauSuiviColis as $suivi)
        {
            $tableauEtat []= $suivi->getEtat()->getCodeEtat();  
        }

        if  (! in_array(999, $tableauEtat )) 
        {   
            $suiviColis = new SuiviColis();
            //on crée un nouvel objet "SuiviColis" qu'on hydrate avec le $colis, le $etat et la dateTime
            $suiviColis ->setColis($colis)
                        ->setEtat($etat)
                        ->setDateSuiviColis(new dateTime())
            ;    
            
            $user = $this->getUser();

            $colis->setNoteColis($colis->getNoteColis().'<br> -  '  .$user->getNomLogistique().' '.$user->getPrenomLogistique().' ('.date('d.m.Y H:m').') : <strong>Closed manually</strong>'  );
            
            $manager->persist($suiviColis);
            $manager->persist($colis);
            $manager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('The parcel has been closed').' !'
            ); 
        }
       
       return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Permet de marquer manuellement un objet "Colis" "En attente d'informations (enquête en cours)", 
     * 
     * @Route("/logistique/colis/{id}/litige", name="logistique_colis_litige")
     * 
     * @param Colis $colis
     * @param EntityManagerInterface $manager
     * @param EtatRepository $repoEtat
     * @param Request $request
     * 
     * @return Response
     */
    public function ajouterSuiviColis008(Colis $colis, EntityManagerInterface $manager, EtatRepository $repoEtat, Request $request): Response 
    {
        //on récupère l'objet "Etat" correspondant à l'attribut $codeEtat "999" (colis livré)
        $user = $this->getUser();
        $etat = $repoEtat->findOneBy(['codeEtat' => '008']);
        $tableauEtat = array(); 
        $tableauSuiviColis = $colis->getSuiviColis();
        
        // Pour chaque suivi du tableau  $tableauSuiviColis
        foreach ($tableauSuiviColis as $suivi)
        {
            //On récupère le codeEtat dans $tableauEtat
            $tableauEtat []= $suivi->getEtat()->getCodeEtat();
            // on stock le suivi correspondant à l'etat "livré'  dans (pour le supprimer) et ajouter une note 
            if ( $suivi->getEtat()->getCodeEtat() == 999 )
            {
                $suiviLivre = $suivi;
                $manager->remove($suiviLivre);
                $manager->flush();

                $colis->setNoteColis($colis->getNoteColis().'<br> -  '  .$user->getNomLogistique().' '.$user->getPrenomLogistique().' ('.date('d.m.Y H:m').') : <strong>Untaged "delivered" because issue</strong>'  );
            } 
        }

        if  (! in_array("008", $tableauEtat )) 
        {   
            $suiviColis = new SuiviColis();
            //on crée un nouvel objet "SuiviColis" qu'on hydrate avec le $colis, le $etat et la dateTime
            $suiviColis ->setColis($colis)
                        ->setEtat($etat)
                        ->setDateSuiviColis(new dateTime())
            ;  
            
            $colis->setNoteColis($colis->getNoteColis().'<br> -  '  .$user->getNomLogistique().' '.$user->getPrenomLogistique().' ('.date('d.m.Y H:m').') : <strong>Waiting for more information (internal investigation)</strong>'  );
            
            $manager->persist($suiviColis);
            $manager->persist($colis);
            $manager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans("The parcel has been tagged as 'Waiting for more information'"). ' !'
            ); 
        }
       
       return $this->redirect($request->headers->get('referer'));
    }
    //***********************************************************************************************************************************************//
    //******************************************************** Fin: Section SuiviColiss *************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //******************************************************* Début: Section Requete **************************************************************//
    //***********************************************************************************************************************************************//

    /** 
     * Permet de soumetre une "Requete" au service secrétariat
     * 
     * @Route("/logistique/requete/service/{service}", name="logistique_requete")
     * 
     * @param string $service
     * @param Request $request
     * @param ContactNotification $contactNotification
     * @param SluggerInterface $slugger
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function requete($service, Request $request, ContactNotification $contactNotification, SluggerInterface $slugger, EntityManagerInterface $manager): Response 
    {
        $user = $this->getUser();
        $contact = new Contact();
       
        $contact->setNom($user->getNomLogistique());
        $contact->setPrenom($user->getPrenomLogistique());
        $contact->setEmail($user->getEmailLogistique());
        $contact->setService($service);
        
        $form = $this->createForm(ContactType::class, $contact );
        $form->handleRequest($request);

        if($form->isSubmitted())
        {
            $uploadedFile = $form->get('fichier')->getData();

            if (filesize($uploadedFile) > 4096000 )
            {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('The file is too large').' !'
                );

                return $this->redirectToRoute("logistique_requete");
            }
        }
        
        if($form->isSubmitted() && $form->isValid())
        {
            $uploadedFile = $form->get('fichier')->getData();

            if ($uploadedFile)
            {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Permet de créer une url propre(sans espaces, sans caractère spéciaux)
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'. date('d-m-Y-His') .'.'.$uploadedFile->guessExtension();

                try 
                {
                    $uploadedFile->move('../uploads/logistique/'.$user->getId().'/', $newFilename );    
                } catch (FileException $e) 
                {
                    // ... handle exception if something happens during file upload
                }

                $contact->setNomFichier($newFilename, $user->getId());
            }

            $contactNotification->sendEmail($contact , $user->getId());

            $this->addFlash(
                'success',
                $this->translator->trans('The message has been sent successfully').' !'
            );

            //persiste le mail dans requete
            $requete = new Requete;
            $requete->setMessageRequete($contact->getMessage()) 
                    ->setObjetRequete($contact->getSujet())
                    ->setLogistique($user)
                    ->setServiceRequete($contact->getService())
                    ->setRequerantRequete('logistique');

            if ($uploadedFile) 
            {
                $requete->setFichierUrlRequete('../uploads/logistique/'.$user->getId().'/'.$newFilename);
            }  
           
            $manager->persist($requete);
            $manager->flush(); 
            
           return $this->redirectToRoute("logistique_requete_liste");
        }

        if ($service === 'secretariat') $titre = $this->translator->trans('Contact secretariat');
        else $titre = $this->translator->trans('Contact administration');

        return $this->render('logistique/requete/contact.html.twig', [
            'titre' => $titre,
            'form' => $form->createView(),
            'user' => $user,
            'service' => $contact->getService()   
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "Requete" de l'objet "Logistique" sous forme de tableau (éventuelement par statutRequete)
     * 
     * @Route("/logistique/requete/liste/{statut<ouvert|ferme>?}", name="logistique_requete_liste")
     * 
     * @param string $statut|null
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequete($statut, RequeteRepository $repoRequete): Response 
    {
        $user = $this->getUser();

        if ($statut == 'ouvert') $requetes = $repoRequete->findBy(['statutRequete' => 1 , 'logistique' =>$user] );
        elseif ($statut == 'fermer') $requetes = $repoRequete->findBy(['statutRequete' => 0 , 'logistique' =>$user] );
        else $requetes = $user->getRequetes();

        return $this->render('logistique/requete/liste.html.twig', [     
            'titre' => $this->translator->trans('Requests list'),
            'requetes' => $requetes
        ]);
    } 

    /**
     * Permet d'afficher la liste de tous les objets "Requete" en cours sous forme de tableau (éventuelement par requerantRequete)
     * 
     * @Route("/logistique/requete/ouvert/{requerant<secretariat|chauffeur>?}", name="logistique_requete_ouvert")
     * 
     * @param string $requerant|null
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequeteOuvert($requerant, RequeteRepository $repoRequete): Response 
    {
        if ($requerant) $requetesEnCours = $repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'logistique', 'requerantRequete' => $requerant] );
        else $requetesEnCours = $repoRequete->findBy(['statutRequete' => 1, 'serviceRequete' => 'logistique', ] );
       
        return $this->render('logistique/requete/ouvert.html.twig', [
            'titre' => $this->translator->trans('Pending request'),
            'requetesEnCours' => $requetesEnCours
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "Requete" cloturés sous forme de tableau
     * 
     * @Route("/logistique/requete/ferme", name="logistique_requete_ferme")
     * 
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequeteFerme(RequeteRepository $repoRequete): Response 
    {
        $requetesArchive = $repoRequete->findBy(['statutRequete' => 0 , 'serviceRequete' => 'logistique'] );
 
        return $this->render('logistique/requete/ferme.html.twig', [
            'requetesArchive' => $requetesArchive, 
            'titre' => $this->translator->trans('Closed request')    
        ]);
    }

    /**
     * Permet de traiter un objet "Requete"
     * 
     * @Route("/logistique/requete/{id}/traiter", name="logistique_requete_traiter")
     * 
     * @param Requete $requete
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function traiterRequete(Requete $requete, Request $request, EntityManagerInterface $manager): Response 
    {
        // Interdire la modification d'une requête clôturée
        if (!$requete->getStatutRequete())
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('The request is archived, and not modifiable').' !'
            ); 

            return $this->redirectToRoute('logistique_requete_ferme');
        }    

        $form = $this->createForm(RequeteType::class, $requete);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // On récupère le message et l'utilisateur connecté, on ajoute l'auteur et on reinjecte le message (Sonneville)
            $user = $this->getUser();
            $message = $requete->getNoteRequete();
           
            $requete->setStatutRequete(false)
                    ->setDateStatutRequete(new \DateTime())
                    ->setNoteRequete($message.'<br>'.$this->translator->trans('Processed by').' '.$user->getNomLogistique().' '.$user->getPrenomLogistique().'');
            
            $manager->persist($requete);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The request').' "'."<strong>{$requete->getObjetRequete()}</strong>".'" '.$this->translator->trans('has been processed').' !'
            ); 
            
           return $this->redirectToRoute('logistique_requete_ferme');
        }
 
        return $this->render('logistique/requete/traiter.html.twig', [
            'requete' => $requete,
            'titre' => $this->translator->trans('Process a request'),
            'form' => $form->createView()   
        ]);
    }

    /**
     * Permet de télécharger le fichier envoyé lors d'une requête
     * 
     * @Route("/logistique/fichier/{id}", name="logistique_requete_fichier")
     * 
     * @param Requete $requete
     * @param Request $request
     * 
     * @return Response
     */
    public function telechargerFichierRequete(Requete $requete, Request $request): Response 
    {
        if (file_exists($requete->getFichierUrlRequete()))
        {
            $file = new File($requete->getFichierUrlRequete(), true);

            return $this->file($file);
        }
        else
        {
            $this->addFlash(
                'danger',
                $this->translator->trans('The file doesn\'t exist').' !'
            );
        }
        
        return $this->redirect($request->headers->get('referer'));
    }

    //***********************************************************************************************************************************************//
    //******************************************************** Fin: Section Requete *****************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
}
