<?php

namespace App\Controller; 

use DateTime;
use DateInterval;
use App\Entity\Amende;
use App\Entity\Requete;
use App\Entity\Vehicule;
use App\Form\AmendeType;
use App\Entity\Chauffeur;
use App\Entity\Entretien;
use App\Form\ContactType;
use App\Form\RequeteType;
use App\Form\VehiculeType;
use App\Entity\Affectation;
use App\Form\ChauffeurType;
use App\Form\EntretienType;
use App\Entity\ModeleVehicule;
use App\Entity\PermisConduire;
use App\Form\ModeleVehiculeType;
use App\Entity\ControleTechnique;
use App\Form\ControleTechniqueType;
use App\Repository\AmendeRepository;
use App\Repository\RequeteRepository;
use App\Service\Notification\Contact;
use App\Repository\VehiculeRepository;
use App\Entity\CategoriePermisConduire;
use App\Repository\ChauffeurRepository;
use App\Repository\EntretienRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CategoriePermisConduireType;
use App\Repository\AffectationRepository;
use phpDocumentor\Reflection\Types\Integer;
use App\Repository\ModeleVehiculeRepository;
use App\Repository\PermisConduireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ControleTechniqueRepository;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Notification\ContactNotification;
use App\Repository\CategoriePermisConduireRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Secretariat\SecretariatVehiculeService;
use App\Service\Secretariat\SecretariatStatistiqueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecretariatController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator){
        $this->translator = $translator;
    }

    /**
     * Permer d'afficher le tableau de bord de l'espace secrétariat
     * 
     * @Route("/secretariat/dashboard", name="secretariat_dashboard")
     * 
     * @param SecretariatStatistiqueService $secretariatStatistiqueService
     * 
     * @return Response
     */
    public function dashboard(SecretariatStatistiqueService $secretariatStatistiqueService)
    {
        // Si l'utilisateur a le rôle "ROLE_NEW_USER" on le redirige vers la page de modification du mot de passe
        $user = $this->getUser();
        if (in_array("ROLE_NEW_USER", $user->getRoles()))
        {
            $this->addFlash(
                'danger',
                $this->translator->trans('Password has to be changed') 
            );

            return $this->redirectToRoute("secretariat_password");
        } 

        $satistiques =   $secretariatStatistiqueService->getStatistique();
       
        return $this->render('secretariat/dashboard.html.twig', [
            'titre' => $this->translator->trans('Dashboard'),
            'secretariat' => $this->getUser(),
            'statistiques' => $satistiques
        ]);
    }   

     /**
     * Affiche la page d'aide secretariat
     * 
     * @Route("/secretariat/help", name="secretariat_help")
     *  
     * @param TranslatorInterface $translator
     * 
     * @return Response
     */
    public function helpSecretariat(TranslatorInterface $translator): Response
    {
        return $this->render('secretariat/help.html.twig',[
            'titre' => $translator->trans('Secretariat help')
            ]
        );
    }

    //***********************************************************************************************************************************************//
    //*********************************************************** Debut: Section Amende *************************************************************//
    //***********************************************************************************************************************************************//
    
    /**
     * Permet d'afficher la liste de touts les objets "Amende" sous forme de tableau 
     * 
     * @Route("/secretariat/amende/liste/{month<[0-9]{2}>?}/{year<[0-9]{4}>?}", name="secretariat_amende_liste")
     * 
     * @param string $month|null
     * @param string $year|null
     * @param AmendeRepository $repoAmende
     * @param AffectationRepository $repoAffectation
     * 
     * @return Response
     */
    public function listeAmende($month = null, $year = null, AmendeRepository $repoAmende, AffectationRepository $repoAffectation): Response
    {   
        $affectations = array(); 

        if ($month && $year) $amendes = $repoAmende->getAmendeMois($month, $year);
        else $amendes = $repoAmende->findBy([], ['dateAmende'=>'DESC']);   
        
        foreach ($amendes as $amende)
        {
            $affectation = new Affectation();
            $affectation = $repoAffectation->findOneBy(['dateAffectation' => $amende->getDateAmende(), 'vehicule' => $amende->getVehicule()], []);
            if ($affectation)
            {
                $affectations[] = clone $affectation;
            }  
        }
        
        return $this->render('secretariat/amende/liste.html.twig', [
            'affectations' => $affectations,
            'amendes' => $amendes,       
            'titre' => $this->translator->trans('Fines list')
        ]);
    }

    /**
     * Permet de suprimer un objet "Amende"
     * 
     * @Route("/secretariat/amende/{id}/supprimer", name="secretariat_amende_supprimer")
     * 
     * @param Amende $amende
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function supprimerAmende(Amende $amende, EntityManagerInterface $manager): Response
    {
        $manager->remove($amende);
        $manager->flush();
       
        $this->addFlash(
            'success',
            $this->translator->trans('The fine has been deleted').' !'
        );

        return $this->redirectToRoute("secretariat_amende_liste");
    }

    /**
     * Permet d'ajouter un objet "Amende"
     * 
     * @Route("/secretariat/amende/ajouter", name="secretariat_amende_ajouter")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterAmende(Request $request, EntityManagerInterface $manager): Response
    {
        $amende = new Amende();
        
        $form = $this->createForm(AmendeType::class, $amende);
        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($amende);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The fine has been added').' !'
            ); 
              
            return $this->redirectToRoute('secretariat_amende_details', [ 'id' => $amende->getId()] );
        }

        return $this->render('secretariat/amende/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add a fine'),
            'form' => $form->createView()  
        ]);
    }

    /**
     * Permet de filtrer le champ "immatriculationVehicule" lors de l'ajout d'un objet "Amende"
     * 
     * @Route("/secretariat/vehicule/filtrer", name="secretariat_vehicule_filtrer")
     * 
     * @param VehiculeRepository $repoVehicule
     * @param Request $request
     * 
     */
    public function getVehiculeApi(VehiculeRepository $repoVehicule, Request $request): Response
    {
        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) 
        {
            $immatriculation = [];
            $vehicules = $repoVehicule->findAllMatching($request->request->get('keyword'));
        
            foreach ($vehicules as $vehicule)
            {
                $immatriculation[] = $vehicule->getImmatriculationVehicule();
            }

            return $this->json(['vehicules' => $immatriculation], 200, []); 
        }   
    }

    /**
     * Permet de modifier un objet "Amende"
     * 
     * @Route("/secretariat/amende/{id}/modifier", name="secretariat_amende_modifier")
     * 
     * @param Amende $amende
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierAmende(Amende $amende, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AmendeType::class, $amende);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($amende);
            $manager->flush();          
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The fine has been edited').' !'
            ); 
            
            return $this->redirectToRoute('secretariat_amende_details', [ 'id' => $amende->getId()] );
        }

        return $this->render('secretariat/amende/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit fine') .' : <strong>'.$amende->getNumAmende().'</strong>',
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher un objet "Amende"
     * 
     * @Route("/secretariat/amende/{id}", name="secretariat_amende_details")
     * 
     * @param Amende $amende
     * @param AffectationRepository $repoAffectation
     * 
     * @return Response
     */
    public function detailsAmende(Amende $amende, AffectationRepository $repoAffectation): Response
    {
        $date = $amende->getDateAmende();
        $affectation = $repoAffectation->findOneBy(['dateAffectation' => $date, 'vehicule' => $amende->getVehicule()  ], []);

        return $this->render('secretariat/amende/details.html.twig', [
            'titre' => $this->translator->trans('Details of the fine').' : <strong>'.$amende->getNumAmende().'</strong>',
            'amende' => $amende, 
            'affectation' => $affectation
        ]);
    }

    //***********************************************************************************************************************************************//
    //************************************************************** Fin: Section Amende ************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //*********************************************************** Debut: Section Chauffeur **********************************************************//
    //***********************************************************************************************************************************************//
    
    /**
     * Permet d'afficher la liste de tous les objets "Chauffeur" sous forme de tableau
     * 
     * @Route("/secretariat/chauffeur/liste/{statut<0|1>}", name="secretariat_chauffeur_liste")
     * 
     * @param string $statut|null
     * @param ChauffeurRepository $repoChauffeur
     * 
     * @return Response
     */
    public function listeChauffeur($statut = null , ChauffeurRepository $repoChauffeur): Response
    {  
        if ($statut === null) $chauffeurs = $repoChauffeur->findAll();
        else $chauffeurs = $repoChauffeur->findBy(['statutChauffeur' => $statut]);
 
        return $this->render('secretariat/chauffeur/liste.html.twig', [
            'chauffeurs' => $chauffeurs, 
            'titre' => $statut ? $this->translator->trans('Drivers list') : $this->translator->trans('Disabled drivers list')
        ]);
    }

    /**
     * Permet de supprimer un objet "Chauffeur"
     * 
     * @Route("/secretariat/chauffeur/{id}/supprimer", name="secretariat_chauffeur_supprimer")
     * 
     * @param Chauffeur $chauffeur
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function supprimerChauffeur(Chauffeur $chauffeur, EntityManagerInterface $manager): Response
    {
        $manager->remove($chauffeur);
        $manager->flush();
       
        $this->addFlash(
            'success',
            $this->translator->trans('The driver has been deleted').' !'
        );
        
        return $this->redirectToRoute("secretariat_chauffeur_liste");
    }

    /**
     * Permet d'ajouter un objet "Chauffeur"
     * 
     * @Route("/secretariat/chauffeur/ajouter", name="secretariat_chauffeur_ajouter")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * 
     * @return Response
     */
    public function ajouterChauffeur(Request $request, EntityManagerInterface $manager,UserPasswordEncoderInterface $encoder): Response
    {
        $chauffeur = new Chauffeur();
        $form = $this->createForm(ChauffeurType::class, $chauffeur);
        
        $chauffeur->setPassword($encoder->encodePassword($chauffeur,'password'));
        $form->handleRequest($request);
       
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($chauffeur);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The driver has been added').' !'
            ); 
              
            return $this->redirectToRoute('secretariat_chauffeur_details', [ 'id' => $chauffeur->getId()] );
        }

        return $this->render('secretariat/chauffeur/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Driver add'),
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier un objet "Chauffeur"
     * 
     * @Route("/secretariat/chauffeur/{id}/modifier", name="secretariat_chauffeur_modifier")
     * 
     * @param Chauffeur $chauffeur
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierChauffeur(Chauffeur $chauffeur, Request $request, EntityManagerInterface $manager): Response
    {
        // Empêche la modification d'une archive chauffeur
        if ( !$chauffeur->getstatutChauffeur() ) 
        {
            $this->addFlash(
                'danger', 
                $this->translator->trans('No changes are possible until driver activation').' !'
            );
            return $this->redirectToRoute('secretariat_chauffeur_details', ['id'=> $chauffeur->getId()] );
        }

        $form = $this->createForm(ChauffeurType::class, $chauffeur);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($chauffeur);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The driver has been edited').' !'
            ); 
            
            return $this->redirectToRoute('secretariat_chauffeur_details', [ 'id' => $chauffeur->getId()] );
        }

        return $this->render('secretariat/chauffeur/ajouter-modifier.html.twig', [
            'form' => $form->createView(),
            'titre' => $this->translator->trans('Edit driver') .' : <strong>'. $chauffeur->getNomChauffeur()." ". $chauffeur->getPrenomChauffeur().'</strong>'
        ]);
    }

    /**
     * Permet d'activer ou de désactiver un objet "Chauffeur" (changement du statutChauffeur)
     * 
     * @Route("/secretariat/chauffeur/statut/{id}", name="secretariat_chauffeur_statut")
     * 
     * @param Chauffeur $chauffeur
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function changerStatutChauffeur(Chauffeur $chauffeur, Request $request, EntityManagerInterface $manager): Response
    {
        if ( $chauffeur->getStatutChauffeur() )
        { 
            $chauffeur->setStatutChauffeur(false);
            $manager->persist($chauffeur);
        }
        else 
        {
            $chauffeur->setStatutChauffeur(true);
            $manager->persist($chauffeur);
        }

        $manager->flush();    
            
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Permet d'afficher les détails d'un objet "Chauffeur"
     * 
     * @Route("/secretariat/chauffeur/{id}", name="secretariat_chauffeur_details")
     * 
     * @param Chauffeur $chauffeur
     * 
     * @return Response
     */
    public function detailsChauffeur(Chauffeur $chauffeur): Response
    {
        return $this->render('secretariat/chauffeur/details.html.twig', [
            'titre' => $this->translator->trans('Details of the driver').' : <strong>'.$chauffeur->getNomChauffeur()." ".$chauffeur->getPrenomChauffeur().'</strong>',
            'chauffeur' => $chauffeur 
        ]);
    }

    //***********************************************************************************************************************************************//
    //*********************************************************** Fin : Section Chauffeur ***********************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //*********************************************************** Debut: Section Vehicule ***********************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'afficher la liste de tous les objets "Vehicule" sous forme de tableau
     * 
     * @Route("/secretariat/vehicule/liste/{statut<0|1>?}", name="secretariat_vehicule_liste")
     * 
     * @param string $statut|null
     * @param VehiculeRepository $repoVehicule
     * 
     * @return Response
     */
    public function listeVehicule($statut = null, VehiculeRepository $repoVehicule): Response
    { 
        if ($statut === null) $vehicules = $repoVehicule->findAll();
        else $vehicules = $repoVehicule->findBy(['statutVehicule' => $statut]);
 
        return $this->render('secretariat/vehicule/liste.html.twig', [
            'vehicules' => $vehicules, 
            'titre' => $statut ? $this->translator->trans('Vehicles list') : $this->translator->trans('Disabled vehicles list')
        ]);
    }

    /**
     * Permet d'afficher la liste des objets "Vehicule" dont le dernier CT est expiré
     * 
     * @Route("/secretariat/vehicule/liste/ct/expire", name="secretariat_vehicule_liste_expire")
     *
     * @param SecretariatVehiculeService $secretariatVehiculeService
     * 
     * @return Response
     */
    public function listeVehiculeControleTechniqueExpire(SecretariatVehiculeService $secretariatVehiculeService): Response
    { 
        $vehicules = $secretariatVehiculeService->getVehiculeControleTechniqueExpire();
 
        return $this->render('secretariat/vehicule/liste.html.twig', [
            'vehicules' => $vehicules, 
            'titre' => $this->translator->trans('Vehicles list').' : '.$this->translator->trans('Vehicle inspection expired')
        ]);
    }

    /**
     * Permet d'afficher la liste des objets "Vehicule" dont le dernier CT est refusé
     * 
     * @Route("/secretariat/vehicule/liste/ct/refuse", name="secretariat_vehicule_liste_refuse")
     *
     * @param SecretariatVehiculeService $secretariatVehiculeService
     * 
     * @return Response
     */
    public function listeVehiculeControleTechniqueRefuse(SecretariatVehiculeService $secretariatVehiculeService): Response
    { 
        $vehicules = $secretariatVehiculeService->getVehiculeControleTechniqueRefuse();
        
        return $this->render('secretariat/vehicule/liste.html.twig', [
            'vehicules' => $vehicules, 
            'titre' => $this->translator->trans('Vehicles list').' : '.$this->translator->trans('Vehicle inspection refused')
        ]);
    }

    /**
     * Permet d'afficher la liste des objets "Vehicule" dont le CT expire bientôt
     * 
     * @Route("/secretariat/vehicule/liste/ct/bientot", name="secretariat_vehicule_liste_bientot")
     *
     * @param SecretariatVehiculeService $secretariatVehiculeService
     * 
     * @return Response
     */
    public function listeVehiculeControleTechniqueBientot(SecretariatVehiculeService $secretariatVehiculeService): Response
    { 
        $vehicules = $secretariatVehiculeService->getVehiculeControleTechniqueBientot();

        return $this->render('secretariat/vehicule/liste.html.twig', [
            'vehicules' => $vehicules, 
            'titre' => $this->translator->trans('Vehicles list').' : '.$this->translator->trans('Vehicle inspection expires soon')
        ]);
    }

    /**
     * Permet d'afficher la liste des objets "Vehicule" dont l'entretien doit être fait
     * 
     * @Route("/secretariat/vehicule/liste/entretien", name="secretariat_vehicule_liste_entretien")
     *
     * @param SecretariatVehiculeService $secretariatVehiculeService
     * 
     * @return Response
     */
    public function listeVehiculeEntretien(SecretariatVehiculeService $secretariatVehiculeService): Response
    { 
        $vehicules = $secretariatVehiculeService->getVehiculeEntretien();

        return $this->render('secretariat/vehicule/liste.html.twig', [
            'vehicules' => $vehicules, 
            'titre' => $this->translator->trans('Vehicles list').' : '.$this->translator->trans('maintenance required')
        ]);
    }

    /**
     * Permet d'ajouter un objet "Vehicule"
     * 
     * @Route("/secretariat/vehicule/ajouter", name="secretariat_vehicule_ajouter")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterVehicule(Request $request, EntityManagerInterface $manager): Response
    {
        $vehicule =  new Vehicule();
    
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($vehicule);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The vehicle has been added').' !'
            ); 
              
            return $this->redirectToRoute('secretariat_vehicule_details', [ 'id' => $vehicule->getId()] );
        }

        return $this->render('secretariat/vehicule/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add a vehicle'),
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier un objet "Vehicule"
     * 
     * @Route("/secretariat/vehicule/{id}/modifier", name="secretariat_vehicule_modifier")
     * 
     * @param Vehicule $vehicule
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierVehicule(Vehicule $vehicule, Request $request, EntityManagerInterface $manager): Response
    {
        //Empêche la modification d'une archive vehicule
        if ( !$vehicule->getstatutVehicule() ) 
        {
            $this->addFlash(
                'danger', 
                $this->translator->trans('No changes are possible until vehicle activation').' !'
            );

            return $this->redirect($request->headers->get('referer'));
        }

        $form = $this->createForm(vehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {    
            $manager->persist($vehicule);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The vehicle has been edited').' !'
            ); 
            
            return $this->redirectToRoute('secretariat_vehicule_details', [ 'id' => $vehicule->getId()] );
        }

        return $this->render('secretariat/vehicule/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit vehicle').' : <strong>'.$vehicule->getImmatriculationVehicule().'</strong>',
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'activer ou de desactiver un objet "Vehicule" (changement du statutVehicule)
     * 
     * @Route("/secretariat/vehicule/statut/{id}", name="secretariat_vehicule_statut")
     * 
     * @param Vehicule $vehicule
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function changerStatutVehicule(Vehicule $vehicule, Request $request, EntityManagerInterface $manager): Response
    {
        if ($vehicule->getStatutVehicule() )
        { 
            $vehicule->setStatutVehicule(false);
            $manager->persist($vehicule);

            $this->addFlash(
                'success', 
                $this->translator->trans('The vehicle has been disabled').' !'
            );    
        }
        else 
        {
            $vehicule->setStatutVehicule(true);
            $manager->persist($vehicule);
            $this->addFlash(
                'success', 
                $this->translator->trans('The vehicle has been enabled').' !'
            );    
        }

        $manager->flush();    
            
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Permet d'afficher un objet "Vehicule" et d'acceder au infos completes de l'objet "Vehicule"
     * 
     * @Route("/secretariat/vehicule/{id}", name="secretariat_vehicule_details")
     * 
     * @param Vehicule $vehicule
     * 
     * @return Response
     */
    public function detailsVehicule(Vehicule $vehicule): Response 
    {
        return $this->render('secretariat/vehicule/details.html.twig', [
            'titre' => $this->translator->trans('Vehicle details').' : <strong>'.$vehicule->getImmatriculationVehicule().'</strong>',
            'vehicule' => $vehicule 
        ]);
    }

    //***********************************************************************************************************************************************//
    //*********************************************************** Fin : Section Vehicule ************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //******************************************************* Debut: Section ModeleVehicule *********************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'afficher la liste de tous les objets "ModeleVehicule" sous forme de tableau
     * 
     * @Route("/secretariat/modele-vehicule/liste", name="secretariat_modele-vehicule_liste")
     * 
     * @param ModeleVehiculeRepository $repoModeleVehicule
     * 
     * @return Response
     */
    public function listeModeleVehicule(ModeleVehiculeRepository $repoModeleVehicule): Response
    {
        $modeleVehicules = $repoModeleVehicule->findBy([], ['id'=>'DESC']);
 
        return $this->render('secretariat/modele-vehicule/liste.html.twig', [
            'modeleVehicules' => $modeleVehicules, 
            'titre' => $this->translator->trans('Models list')  
        ]);
    }

    /**
     * Permet d'ajouter un objet "ModeleVehicule"
     * 
     * @Route("/secretariat/modele-vehicule/ajouter", name="secretariat_modele-vehicule_ajouter")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterModeleVehicule(Request $request, EntityManagerInterface $manager): Response
    {
        $modeleVehicule =  new ModeleVehicule();
    
        $form = $this->createForm(ModeleVehiculeType::class, $modeleVehicule);
        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        { 
            $manager->persist($modeleVehicule);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The model has been added').' !'
            ); 
              
            return $this->redirectToRoute('secretariat_modele-vehicule_details', [ 'id' => $modeleVehicule->getId()] );
        }

        return $this->render('secretariat/modele-vehicule/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add a model'),
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier un objet "ModeleVehicule"
     * 
     * @Route("/secretariat/modele-vehicule/{id}/modifier", name="secretariat_modele-vehicule_modifier")
     * 
     * @param ModeleVehicule $modeleVehicule
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * 
     * @return Response
     */
    public function modifierModeleVehicule(ModeleVehicule $modeleVehicule, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(ModeleVehiculeType::class, $modeleVehicule);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($modeleVehicule);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The model has been edited').' !'
            ); 
            
            return $this->redirectToRoute('secretariat_modele-vehicule_details', [ 'id' => $modeleVehicule->getId()] );
        }

        return $this->render('secretariat/modele-vehicule/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit model').' : <strong>'.$modeleVehicule->getNomModeleVehicule().'</strong>',
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher un objet "ModeleVehicule"
     * 
     * @Route("/secretariat/modele-vehicule/{id}", name="secretariat_modele-vehicule_details")
     * 
     * @param ModeleVehicule $modeleVehicule
     * 
     * @return Response
     */
    public function detailsModeleVehicule(ModeleVehicule $modeleVehicule) : Response
    {
        return $this->render('secretariat/modele-vehicule/details.html.twig', [
            'titre' => $this->translator->trans('Model details').' : <strong>'. $modeleVehicule->getNomModeleVehicule().'</strong>',
            'modeleVehicule' => $modeleVehicule 
        ]);
    }

    //***********************************************************************************************************************************************//
    //********************************************************** Fin: Section ModeleVehicule ********************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //********************************************************* Debut: Section PermisConduire *******************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'afficher la liste de tous les objets "PermisConduire" sous forme de tableau
     * 
     * @Route("/secretariat/permis-conduire/liste", name="secretariat_permis-conduire_liste")
     * 
     * @param PermisConduireRepository $repoPermisConduire
     * 
     * @return Response
     */
    public function listePermisConduire(PermisConduireRepository $repoPermisConduire): Response
    {
        $permisConduires = $repoPermisConduire->findAll();
 
        return $this->render('secretariat/permis-conduire/liste.html.twig', [
            'permisConduires' => $permisConduires, 
            'titre' => $this->translator->trans('Driver\'s licenses list')    
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "PermisConduire" arrivant à échéance
     * 
     * @Route("/secretariat/permis-conduire/liste/bientot", name="secretariat_permis-conduire_liste_bientot")
     * 
     * @param PermisConduireRepository $repoPermisConduire
     * 
     * @return Response
     */
    public function listePermisConduireBientot(PermisConduireRepository $repoPermisConduire): Response
    {
        $permisConduires = $repoPermisConduire->getPermisConduireBientot();
 
        return $this->render('secretariat/permis-conduire/liste.html.twig', [
            'permisConduires' => $permisConduires, 
            'titre' => $this->translator->trans('Driver\'s licenses list').' : '. $this->translator->trans('expires soon')    
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "PermisConduire" expiré
     * 
     * @Route("/secretariat/permis-conduire/liste/expire", name="secretariat_permis-conduire_liste_expire")
     * 
     * @param PermisConduireRepository $repoPermisConduire
     * 
     * @return Response
     */
    public function listePermisConduireExpire(PermisConduireRepository $repoPermisConduire): Response
    {
        $permisConduires = $repoPermisConduire->getPermisConduireExpire();
 
        return $this->render('secretariat/permis-conduire/liste.html.twig', [
            'permisConduires' => $permisConduires, 
            'titre' => $this->translator->trans('Driver\'s licenses list').' : '. $this->translator->trans('expired')    
        ]);
    }

    /**
     * Permet de suprimer un objet "PermisConduire"
     * 
     * @Route("/secretariat/permis-conduire/{id}/supprimer", name="secretariat_permis-conduire_supprimer")
     * 
     * @param PermisConduire $permisConduire
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function supprimerPermisConduire(PermisConduire $permisConduire, EntityManagerInterface $manager): Response
    { 
        $manager->remove($permisConduire);
        $manager->flush();
       
        $this->addFlash(
            'success',
            $this->translator->trans('The driver\'s license has been deleted').' !'
        );
        
        return $this->redirectToRoute("secretariat_permis-conduire_liste");
    }

    //***********************************************************************************************************************************************//
    //********************************************************** Fin: Section PermisConduire ********************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //**************************************************** Debut: Section CategoriePermisConduire ***************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'afficher la liste de tous les objets "CategoriePermisConduire" sous forme de tableau
     * 
     * @Route("/secretariat/categorie-permis-conduire/liste", name="secretariat_categorie-permis-conduire_liste")
     * 
     * @param CategoriePermisConduireRepository $repoCategoriePermisConduire
     * 
     * @return Response
     */
    public function listeCategoriePermisConduire(CategoriePermisConduireRepository $repoCategoriePermisConduire): Response
    {
        $categoriePermisConduires = $repoCategoriePermisConduire->findAll();
 
        return $this->render('secretariat/categorie-permis-conduire/liste.html.twig', [
            'categoriePermisConduires' => $categoriePermisConduires, 
            'titre' => $this->translator->trans('Categories list')    
        ]);
    }
    
    /**
     * Permet de modifier un objet "CategoriePermisConduire"
     * 
     * @Route("/secretariat/categorie-permis-conduire/{id}/modifier", name="secretariat_categorie-permis-conduire_modifier")
     * 
     * @param CategoriePermisConduire $categoriePermisConduire
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierCategoriePermisConduire(CategoriePermisConduire $categoriePermisConduire, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(CategoriePermisConduireType::class, $categoriePermisConduire);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        { 
            $manager->persist($categoriePermisConduire);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The category has been edited').' !'
            ); 
            
            return $this->redirectToRoute('secretariat_categorie-permis-conduire_details', [ 'id' => $categoriePermisConduire->getId()] );
        }

        return $this->render('secretariat/categorie-permis-conduire/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit category').' : <strong>' . $categoriePermisConduire->getNomCategoriePermisConduire().'</strong>',
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'ajouter un objet "CategoriePermisConduire"
     * 
     * @Route("/secretariat/categorie-permis-conduire/ajouter", name="secretariat_categorie-permis-conduire_ajouter")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterCategoriePermisConduire(Request $request, EntityManagerInterface $manager): Response
    {
        $categoriePermisConduire =  new CategoriePermisConduire();
    
        $form = $this->createForm(CategoriePermisConduireType::class, $categoriePermisConduire);
        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($categoriePermisConduire);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The category has been added').' !'
            ); 
              
            return $this->redirectToRoute('secretariat_categorie-permis-conduire_liste');
        }

        return $this->render('secretariat/categorie-permis-conduire/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add a category'),
            'form' => $form->createView()
        ]);
    }

    //***********************************************************************************************************************************************//
    //***************************************************** Fin: Section CategoriePermisConduire ****************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //*********************************************************** Debut: Section Requete ************************************************************//
    //***********************************************************************************************************************************************//

    /** 
     * Permet de soumettre une requête au service logistique
     * 
     * @Route("/secretariat/requete/service/{service}", name="secretariat_requete")
     * 
     * @param string $service
     * @param Request $request
     * @param ContactNotification $contactNotification
     * @param SluggerInterface $slugger
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function requete($service, Request $request, ContactNotification $contactNotification, SluggerInterface $slugger, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $contact = new Contact();
        $contact->setNom($user->getNomSecretariat());
        $contact->setPrenom($user->getPrenomSecretariat());
        $contact->setEmail($user->getEmailSecretariat());
        $contact->setService($service);
        
        $form = $this->createForm(ContactType::class, $contact );
        
        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        {
            $uploadedFile = $form->get('fichier')->getData();
    
            if ($uploadedFile)
            {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'. date('d-m-Y-His') .'.'.$uploadedFile->guessExtension();

                try 
                {
                    $uploadedFile->move('../uploads/secretariat/'.$user->getId().'/', $newFilename );    
                } catch (FileException $e) 
                {
                    // ... handle exception if something happens during file upload
                }

                $contact->setNomFichier($newFilename, $user->getId());
            }

            $contactNotification->sendEmail($contact , $user->getId());

            $this->addFlash(
                'success',
                $this->translator->trans('Your message has been successfully sent').' !'
            );

            //persiste le mail dans $requete
            $requete = new Requete;
            $requete->setMessageRequete($contact->getMessage()) 
                    ->setObjetRequete($contact->getSujet())
                    ->setSecretariat($user)
                    ->setServiceRequete($contact->getService())
                    ->setRequerantRequete('secretariat');;

            if ($uploadedFile) 
            {
                $requete->setFichierUrlRequete('../uploads/secretariat/'.$user->getId().'/'.$newFilename);
            }  
           
            $manager->persist($requete);
            $manager->flush(); 
            
           return $this->redirectToRoute("secretariat_requete_liste");
        }

        if ($service === 'logistique') $titre = $this->translator->trans('Contact logistics');
        else if ($service === 'administration') $titre = $this->translator->trans('Contact administration');
        else
        {
            $this->addFlash(
                'warning',
                $this->translator->trans('Verify service name').' !'
            );

            return $this->redirectToRoute("secretariat_dashboard");
        } 

        return $this->render('secretariat/requete/contact.html.twig', [
            'titre' => $titre,
            'form' => $form->createView(),
            'user' => $user,
            'service' => $contact->getService()
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "Requete" de l'objet "Secretariat" sous forme de tableau (éventuelement par statutRequete)
     * 
     * @Route("/secretariat/requete/liste/{statut<ouvert|ferme>?}", name="secretariat_requete_liste")
     * 
     * @param string $statut|null
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequete($statut, RequeteRepository $repoRequete): Response
    {
        $user = $this->getUser();

        if ($statut == 'ouvert') $requetes = $repoRequete->findBy(['statutRequete' => 1 , 'secretariat' =>$user] );
        elseif ($statut == 'fermer') $requetes = $repoRequete->findBy(['statutRequete' => 0 , 'secretariat' =>$user] );
        else $requetes = $user->getRequetes();

        return $this->render('secretariat/requete/liste.html.twig', [    
            'titre' => $this->translator->trans('Requests list'),
            'requetes' => $requetes
        ]);
    }    

    /**
     * Permet d'afficher la liste de tous les objets "Requete" en cours sous forme de tableau (éventuelement par requerantRequete)
     * 
     * @Route("/secretariat/requete/ouvert/{requerant<logistique|chauffeur>?}", name="secretariat_requete_ouvert")
     * 
     * @param $requerant|null
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequeteOuvert(RequeteRepository $repoRequete, $requerant ): Response
    {
        if ($requerant) $requetesEnCours = $repoRequete->findBy(['statutRequete' => '1' , 'serviceRequete' => 'secretariat', 'requerantRequete' => $requerant] );
        else $requetesEnCours = $repoRequete->findBy(['statutRequete' => 1, 'serviceRequete' => 'secretariat', ] );
 
        return $this->render('secretariat/requete/ouvert.html.twig', [
            'requetesEnCours' => $requetesEnCours, 
            'titre' => $this->translator->trans('Pending request')    
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "Requete" cloturés sous forme de tableau
     * 
     * @Route("/secretariat/requete/ferme", name="secretariat_requete_ferme")
     * 
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequeteFerme(RequeteRepository $repoRequete ): Response
    {
        $requetesArchive = $repoRequete->findBy(['statutRequete' => 0 , 'serviceRequete' => 'secretariat'] );

        return $this->render('secretariat/requete/ferme.html.twig', [
            'requetesArchive' => $requetesArchive, 
            'titre' => $this->translator->trans('Closed request')    
        ]);
    }

    /**
     * Permet de traiter un objet "Requete"
     * 
     * @Route("/secretariat/requete/{id}/traiter", name="secretariat_requete_traiter")
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

            return $this->redirectToRoute('secretariat_requete_ferme');
        }    

        $form = $this->createForm(RequeteType::class, $requete);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // On récupère le message, on ajoute l'auteur et on réinjecte le message (Sonneville)
            $user = $this->getUser();
            $message = $requete->getNoteRequete();

            $requete->setStatutRequete(false)
                    ->setDateStatutRequete(new \DateTime())
                    ->setNoteRequete($message.'<br>'.$this->translator->trans('Processed by').' '.$user->getNomSecretariat().' '.$user->getPrenomSecretariat().'');
            
            $manager->persist($requete);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The request')." ".$this->translator->trans('has been processed').' !'
            ); 
            
           return $this->redirectToRoute('secretariat_requete_ferme');
        }
 
        return $this->render('secretariat/requete/traiter.html.twig', [
            'requete' => $requete,
            'titre' => $this->translator->trans('Process a request'),
            'form' => $form->createView()   
        ]);
    }

    /**
     * Permet de télécharger le fichier envoyé lors d'une requête
     * 
     * @Route("/secretariat/fichier/{id}", name="secretariat_requete_fichier")
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
            $file = new File($requete->getFichierUrlRequete());
            
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
    //************************************************************ Fin: Section Requete *************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //********************************************************* Debut : Section Entretien ***********************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'afficher la liste de tous les objets "Entretien" sous forme de tableau
     * 
     * @Route("/secretariat/entretien/liste", name="secretariat_entretien_liste")
     * 
     * @param EntretienRepository $repoEntretien
     * 
     * @return Response
     */
    public function listeEntretien(EntretienRepository $repoEntretien): Response
    {
        $entretiens = $repoEntretien->findAll();
 
        return $this->render('secretariat/entretien/liste.html.twig', [
            'entretiens' => $entretiens, 
            'titre' => $this->translator->trans('Maintenances list')    
        ]);
    }

    /**
     * Permet d'ajouter un objet "Entretien" lié à un objet "Vehicule"
     * 
     * @Route("/secretariat/entretien/ajouter/{id}", name="secretariat_entretien_ajouter")
     * 
     * @param Vehicule $vehicule
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterEntretien(Vehicule $vehicule, Request $request, EntityManagerInterface $manager): Response
    {
        $entretien =  new Entretien();
        $entretien->setVehicule($vehicule);

        $form = $this->createForm(EntretienType::class, $entretien);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($entretien);
            $manager->flush();         
            
            $this->addFlash(
                'success',
                $this->translator->trans('The maintenance has been added').' !'
            ); 
              
            return $this->redirectToRoute('secretariat_vehicule_details', [ 'id' => $entretien->getVehicule()->getId()] );
        }

        return $this->render('secretariat/entretien/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add a maintenance'),
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier un objet "Entretien"
     * 
     * @Route("/secretariat/entretien/{id}/modifier", name="secretariat_entretien_modifier")
     * 
     * @param Entretien $entretien
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierEntretien(Entretien $entretien, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(EntretienType::class, $entretien);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        { 
            $manager->persist($entretien);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The maintenance has been edited').' !'
            ); 
            
            return $this->redirectToRoute('secretariat_vehicule_details', [ 'id' => $entretien->getVehicule()->getId()] );
        }

        return $this->render('secretariat/entretien/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit maintenance').' : <strong>' .$entretien->getVehicule()->getImmatriculationVehicule().' ('.$entretien->getDateEntretien()->format("d.m.Y").')</strong>',
            'form' => $form->createView()
        ]);
    }

    //***********************************************************************************************************************************************//
    //********************************************************* Fin: : Section Entretien ************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
 
    //***********************************************************************************************************************************************//
    //***************************************************** Debut : Section ControleTechnique *******************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'afficher la liste de tous les objets "ControleTechnique" sous forme de tableau
     * 
     * @Route("/secretariat/controle-technique/liste/{statut<0|1>?} ", name="secretariat_controle-technique_liste")
     * 
     * @param string $statut|null
     * @param ControleTechniqueRepository $repoControleTechnique
     * 
     * @return Response
     */
    public function listeControleTechnique($statut = null, ControleTechniqueRepository $repoControleTechnique): Response
    {   
        if($statut === null) $controleTechniques = $repoControleTechnique->findAll();
        else $controleTechniques = $repoControleTechnique->findBy(['statutControleTechnique' => $statut]);
 
        return $this->render('secretariat/controle-technique/liste.html.twig', [
            'controleTechniques' => $controleTechniques, 
            'titre' => $this->translator->trans('Vehicle inspections list')    
        ]);
    }

    /**
     * Permet de modifier un objet "ControleTechnique"
     * 
     * @Route("/secretariat/controle-technique/{id}/modifier", name="secretariat_controle-technique_modifier")
     * 
     * @param ControleTechnique $controleTechnique
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierControleTechnique(ControleTechnique $controleTechnique, Request $request, EntityManagerInterface $manager): Response
    {
        $ancienneDate = clone $controleTechnique->getDateControleTechnique();
        $controleTechnique->eraseDateControleTechnique();
        $anciensMessage = $controleTechnique->getRemarqueControleTechnique();
        $controleTechnique->setStatutControleTechnique(false);
        
        $form = $this->createForm(ControleTechniqueType::class, $controleTechnique);
        
        $form->handleRequest($request);
       
        if($form->isSubmitted() && $form->isValid())
        { 
            $dateRepassage = $form->get('dateControleTechnique')->getData() ;

            if ($controleTechnique->getStatutControleTechnique())
            {
                $motifs = $form->get('motifs')->getData() ;
                $stringMotifs = implode(" ,",$motifs);

                $message = 'Refused : '.$dateRepassage->format('d.m.Y').' : '.$stringMotifs;
                $controleTechnique->setRemarqueControleTechnique($anciensMessage.'<br>'. $message);
            }
            else
            {
                $message = 'Passed : '.$dateRepassage->format('d.m.Y');
                $controleTechnique->setRemarqueControleTechnique($anciensMessage.'<br>'. $message);
            }

            $controleTechnique->setDateControleTechnique($ancienneDate);

            $manager->persist($controleTechnique);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The vehicle inspection has been edited').' !'
            ); 
            
            return $this->redirectToRoute('secretariat_vehicule_details', [ 'id' => $controleTechnique->getVehicule()->getId()] );
        }

        return $this->render('secretariat/controle-technique/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit vehicle inspection').' : <strong>'.$controleTechnique->getVehicule()->getImmatriculationVehicule().' ('.$ancienneDate->format("d.m.Y").')</strong>',
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'ajouter un objet "ControleTechnique" lié à un objet "Vehicule"
     * 
     * @Route("/secretariat/controle-technique/ajouter/{id}", name="secretariat_controle-technique_ajouter")
     * 
     * @param Vehicule $vehicule
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterControleTechnique(Vehicule $vehicule, Request $request, EntityManagerInterface $manager): Response
    {
        $controleTechnique =  new ControleTechnique();
        $controleTechnique->setVehicule($vehicule);

        $form = $this->createForm(ControleTechniqueType::class, $controleTechnique);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $datePassage = $form->get('dateControleTechnique')->getData() ;
            if ($controleTechnique->getStatutControleTechnique())
            {
                $motifs = $form->get('motifs')->getData() ;
                $stringMotifs = implode(" ,",$motifs);

                $message = $this->translator->trans('Refused').' : '.$datePassage->format('d.m.Y').' : '.$stringMotifs;
                $controleTechnique->setRemarqueControleTechnique($message);
            }

            $manager->persist($controleTechnique);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The vehicle inspection has been added').' !'
            ); 
              
            return $this->redirectToRoute('secretariat_vehicule_details', [ 'id' => $controleTechnique->getVehicule()->getId()] );
        }

        return $this->render('secretariat/controle-technique/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add a V.I.'),
            'form' => $form->createView()
        ]);
    }

    //***********************************************************************************************************************************************//
    //***************************************************** Fin: : Section ControleTechnique ********************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
}