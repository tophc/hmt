<?php

namespace App\Controller;

use DateTime;
use App\Entity\Etat;
use App\Form\EtatType;
use App\Entity\Requete;
use App\Entity\Chauffeur;
use App\Form\RequeteType;
use App\Entity\Logistique;
use App\Entity\Secretariat;
use App\Form\LogistiqueType;
use App\Form\SecretariatType;
use App\Repository\EtatRepository;
use App\Repository\RequeteRepository;
use App\Repository\ChauffeurRepository;
use App\Repository\LogistiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SecretariatRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Administration\AdministrationUserService;
use App\Service\Administration\AdministrationStatistiqueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdministrationController extends AbstractController
{
    private $translator;
    private $administrationStatistiqueService;
    private $administrationUserService;

    /**
     * Constructeur
     *
     * @param TranslatorInterface $translator
     * @param AdministrationStatistiqueService $administrationStatistiqueService
     * @param AdministrationUserService $administrationUserService
     */
    public function __construct(TranslatorInterface $translator, AdministrationStatistiqueService $administrationStatistiqueService, AdministrationUserService $administrationUserService )
    {
        $this->translator                       = $translator;
        $this->administrationStatistiqueService = $administrationStatistiqueService;
        $this->administrationUserService        = $administrationUserService;  
    }
    
    /**
     * Permet d'afficher le "Dashboard" "Administration"
     * 
     * @Route("/administration", name="administration_dashboard")
     * 
     * @return Response
     */
    public function dashboard(): Response
    {
        $users = $this->administrationUserService->getUserByRole('chauffeur', 'ROLE_DISABLED');

        return $this->render('administration/dashboard.html.twig', [
            'titre'         => $this->translator->trans('Dashboard'),
            'statistiques'  => $this->administrationStatistiqueService->getStatistique(),
        ]);
    }
    
    //***********************************************************************************************************************************************//
    //******************************************************* Début: Section Requete ****************************************************************//
    //***********************************************************************************************************************************************//
    
    /**
     * Permet d'afficher la liste de tous les objets "Requete" en cours sous forme de tableau (éventuelement par requerantRequete)
     * 
     * @Route("/administration/requete/ouvert/{requerant<secretariat|logistique>?}", name="administration_requete_ouvert")
     * 
     * @param string $requerant|null
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequeteOuvert($requerant, RequeteRepository $repoRequete): Response 
    {
        if ($requerant) $requetesEnCours = $repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'administration', 'requerantRequete' => $requerant] );
        else $requetesEnCours = $repoRequete->findBy(['statutRequete' => 1, 'serviceRequete' => 'administration' ] );
       
        return $this->render('administration/requete/ouvert.html.twig', [
            'titre' => $this->translator->trans('Pending request'),
            'requetesEnCours' => $requetesEnCours
        ]);
    }

    /**
     * Permet d'afficher la liste de tous les objets "Requete" cloturés sous forme de tableau
     * 
     * @Route("/administration/requete/ferme", name="administration_requete_ferme")
     * 
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequeteFerme(RequeteRepository $repoRequete): Response 
    {
        $requetesArchive = $repoRequete->findBy(['statutRequete' => 0, 'serviceRequete' => 'administration'] );
 
        return $this->render('administration/requete/ferme.html.twig', [
            'requetesArchive' => $requetesArchive, 
            'titre' => $this->translator->trans('Closed request')    
        ]);
    }

    /**
     * Permet de traiter un objet "Requete"
     * 
     * @Route("/administration/requete/{id}/traiter", name="administration_requete_traiter")
     * 
     * @param Requete $requete
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function traiterRequete(Requete $requete, Request $request, EntityManagerInterface $manager): Response 
    {
        // Interdire la modification d'une requete cloturer
        if (!$requete->getStatutRequete())
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('The request is archived, and not modifiable').' !'
            ); 

            return $this->redirectToRoute('administration_requete_ferme');
        }    

        $form = $this->createForm(RequeteType::class, $requete);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            // On récupère le message, on ajoute l'auteur et on reinjecte le message (Sonneville)
            $user = $this->getUser();
            $message = $requete->getNoteRequete();

            $requete->setStatutRequete(false)
                    ->setDateStatutRequete(new DateTime())
                    ->setNoteRequete($message.'<br>'.$this->translator->trans('Processed by').' '.$user->getNomAdministration().' '.$user->getPrenomAdministration().'');
           
            $manager->persist($requete);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The request').' "'."<strong>{$requete->getObjetRequete()}</strong>".'" '.$this->translator->trans('has been processed').' !'
            ); 
            
           return $this->redirectToRoute('administration_requete_ferme');
        }
 
        return $this->render('administration/requete/traiter.html.twig', [
            'requete' => $requete,
            'titre' => $this->translator->trans('Process a request'),
            'form' => $form->createView()   
        ]);
    }

    /**
     * Permet de télécharger le fichier envoyé lors d'une requête
     * 
     * @Route("/administration/fichier/{id}", name="administration_requete_fichier")
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

    //***********************************************************************************************************************************************//
    //********************************************************* Début: Section user *****************************************************************//
    //***********************************************************************************************************************************************//
    
    /**
     * Permet d'afficher la liste de tous les objets "Logistique", "Secretariat" ou "Chauffeur" sous forme de tableau
     * 
     * @Route("/administration/liste/{service}", name="administration_user_liste")
     * 
     * @param string $service
     * @param ChauffeurRepository $repoChauffeur
     * @param LogistiqueRepository $repoLogistique
     * @param SecretariatRepository $repoSecretariat
     * 
     * @return Response
     */
    public function listeUser($service, ChauffeurRepository $repoChauffeur, LogistiqueRepository $repoLogistique, SecretariatRepository $repoSecretariat): Response
    {  
        if ($service === 'chauffeur') 
        {
            $chauffeurs = $repoChauffeur->findAll();

            return $this->render('administration/user/liste_chauffeur.html.twig', [
                'users' => $chauffeurs, 
                'titre' => $this->translator->trans('Drivers list')
            ]);
        }
        else if ($service === 'secretariat')
        {
            $secretariat = $repoSecretariat->findAll();

            return $this->render('administration/user/liste_secretariat.html.twig', [
                'users' => $secretariat, 
                'titre' => $this->translator->trans('Secretariat list')
            ]);
        }  
        else if ($service === 'logistique')
        {
            $logistique = $repoLogistique->findAll(); 

            return $this->render('administration/user/liste_logistique.html.twig', [
                'users' => $logistique, 
                'titre' => $this->translator->trans('Logistics list')
            ]);  
        }
        else
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('The user type doesn\'t exist')
            ); 

            return $this->redirectToRoute("administration_dashboard");
        }
    }    

    /**
     * Permet d'afficher la liste de tous les objets "Logistique", "Secretariat" ou "Chauffeur" sous forme de tableau en fonction des rôles
     *
     * @Route("/administration/liste/{service}/{role}/", name="administration_user_liste_role")
     * 
     * @param string $service
     * @param string $role
     * @param AdministrationUserService $administrationUserService
     * 
     * @return Response
     */
    public function listeUserByRole($service, $role, AdministrationUserService $administrationUserService)
    {
        $users = $administrationUserService->getUserByRole($service, $role);

        switch ($service)
        {
            case 'chauffeur' : $titre = $this->translator->trans('Drivers list').' : '.$role; break;
            case 'secretariat' : $titre = $this->translator->trans('Secretariat list').' : '.$role; break;
            case 'logistique' : $titre = $this->translator->trans('Logistics list').' : '.$role; break;     
        }

        return $this->render('administration/user/liste_'.$service.'.html.twig', [
            'users' => $users, 
            'titre' => $titre
        ]); 
    } 

    /**
     * Permet d'afficher la liste de tous les objets "Logistique", "Secretariat" ou "Chauffeur" sous forme de tableau en fonction des rôles "absent" (le Rôle "ROLE_ENABLED_USER" n'existe pas)
     *
     * @Route("/administration/liste/{service}/not/{role}", name="administration_user_liste_valide")
     * 
     * @param string $service
     * @param string $role
     * @param AdministrationUserService $administrationUserService
     * 
     * @return Response
     */
    public function listeEnabledUser($service, $role, AdministrationUserService $administrationUserService)
    {
        $users = $administrationUserService->getEnabledUser($service, $role);

        $titre = '';
        switch ($service)
        {
            case 'chauffeur' : $titre = $this->translator->trans('Drivers list untagged').' : '. $role; break;
            case 'secretariat' : $titre = $this->translator->trans('Secretariats list untagged').' : '. $role; break;
            case 'logistique' : $titre = $this->translator->trans('Logistics untagged list').' : '. $role; break;     
        }

        return $this->render('administration/user/liste_'.$service.'.html.twig', [
            'users' => $users, 
            'titre' => $titre
        ]);
    } 

    /**
     * Permet d'ajouter un objet "Logistique" ou "Secretariat"
     * 
     * @Route("/administration/user/ajouter/{service<secretariat|logistique>}", name="administration_user_ajouter")
     * 
     * @param string $service
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * 
     * @return Response
     */
    public function ajouterUser($service, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {
        if ($service === 'secretariat')
        { 
            $user = new Secretariat();
            $form = $this->createForm(SecretariatType::class, $user);
        }
        else if ($service === 'logistique') 
        {
            $user = new Logistique();
            $form = $this->createForm(LogistiqueType::class, $user);
        }
        else
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('Verify service name')
            ); 

            return $this->redirectToRoute("administration_dashboard");
        }
       
        $user->setPassword($encoder->encodePassword($user,'password'));
        $form->handleRequest($request);
       
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($user);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The user has been added').' !'
            ); 
              
            return $this->redirectToRoute('administration_user_liste', ['service' => $service]);
        }

        if ($service === 'logistique') $titre = $this->translator->trans('Logistics user add');
        else if ($service === 'secretariat') $titre = $this->translator->trans('Secretariat user add');
        else
        {
            $this->addFlash(
                'warning',
                $this->translator->trans('Verify service name').' !'
            );
            
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('administration/user/ajouter-modifier.html.twig', [
            'titre' => $titre,
            'form' => $form->createView()
        ]);
    }
  
    /**
     * Permet de réinitialiser le mot de passe d'un utilisateur et lui attribue le rôle "ROLE_NEW_USER"
     *
     * @Route("/administration/{service}/{id}/reinitialiser", name="administration_user_reinitialiser")
     *
     * @param string $service
     * @param string $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @param ChauffeurRepository $repoChauffeur
     * @param SecretariatRepository $repoSecretariat
     * @param LogistiqueRepository $repoLogistique
     * 
     * @return Response
     */
    public function reinitialiserMotDePasse($service, $id, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, ChauffeurRepository $repoChauffeur, SecretariatRepository $repoSecretariat, LogistiqueRepository $repoLogistique ): Response
    {
        // Récupère l'utilisateur en fonction du service
        if ($service === 'chauffeur') $user = $repoChauffeur->find($id);
        else if ($service === 'secretariat') $user = $repoSecretariat->find($id);
        else if ($service === 'logistique') $user = $repoLogistique->find($id);
        else
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('Service doesn\'t exist')
            ); 

            return $this->redirect($request->headers->get('referer'));
        }
        // si l'utilisateur existe et n'a pas le rôle 'ROLE_NEW_USER' ni le rôle 'ROLE_DISABLEd'
        if ($user && ! in_array('ROLE_NEW_USER', $user->getRoles()) && ! in_array('ROLE_DISABLED', $user->getRoles()))
        {
            // Récupère les rôles existant
            $roles = $user->getRoles();
            // On ajoute le rôle 'ROLE_NEW_USER' en première position
            array_unshift($roles, 'ROLE_NEW_USER');
           
            // Réinitialise le 'password' à "password" et on ajoute le nouveau tableau de rôles
            $user->setPassword($encoder->encodePassword($user,'password'))
                 ->setRoles($roles)
            ;

            $manager->persist($user);
            $manager->flush();
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The password has been reset')
            ); 
        }
        else if ($user && in_array('ROLE_DISABLED', $user->getRoles()))
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('User is disabled : enable before resetting password')
            ); 
        }
        // Si l'utilisateur existe et a le rôle 'ROLE_NEW_USER'
        else if ($user && in_array('ROLE_NEW_USER', $user->getRoles()))
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('The password has already been reset')
            ); 
        }
        else
        {
            $this->addFlash(
                'danger', 
                $this->translator->trans('User not found')
            ); 
        }
        
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Permet d'activer ou de desactiver un utilisateur
     *
     * @Route("/administration/{service}/{id}/desactiver", name="administration_user_desactiver")
     * 
     * @param string $service
     * @param int $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ChauffeurRepository $repoChauffeur
     * @param SecretariatRepository $repoSecretariat
     * @param LogistiqueRepository $repoLogistique
     * @param UserPasswordEncoderInterface $encoder
     * 
     * @return Response
     */
    public function disableUser($service, $id, Request $request, EntityManagerInterface $manager, ChauffeurRepository $repoChauffeur, SecretariatRepository $repoSecretariat, LogistiqueRepository $repoLogistique, UserPasswordEncoderInterface $encoder): Response
    {   
        // Récupère l'utilisateur en fonction du service
        if ($service === 'chauffeur') $user = $repoChauffeur->find($id);
        else if ($service === 'secretariat') $user = $repoSecretariat->find($id);
        else if ($service === 'logistique') $user = $repoLogistique->find($id);
        else
        {
            $this->addFlash(
                'warning', 
                $this->translator->trans('Service doesn\'t exist')
            ); 

            return $this->redirect($request->headers->get('referer'));
        }
         // Si l'utilisateur existe et n'a pas le rôle 'ROLE_DISABLED'
        if ($user && ! in_array('ROLE_DISABLED', $user->getRoles()))
        {
            // Réinitialise le 'password' à une valeur aléatoire et on remplace les rôles par le rôle 'ROLE_DISABLED'
            $user->setRoles(["ROLE_DISABLED"])
                 ->setPassword($encoder->encodePassword($user,random_int(1, 10)))
            ;

            $manager->persist($user);
            $manager->flush(); 

            $this->addFlash(
                'success', 
                $this->translator->trans('User has been disabled')
            ); 
        }
        // Si l'utilisateur existe et a le rôle 'ROLE_DISABLED'
        else if ($user && in_array('ROLE_DISABLED', $user->getRoles()))
        {
            // Réinitialise le 'password' à "password" et on initialise les rôles
            $user->setPassword($encoder->encodePassword($user,'password'))
                 ->initializeRoles()
            ;

            $manager->persist($user);
            $manager->flush(); 

            $this->addFlash(
                'success', 
                $this->translator->trans('User has been enabled')
            );
        }
        else
        {
            $this->addFlash(
                'danger', 
                $this->translator->trans('User not found')
            ); 
        }

        return $this->redirect($request->headers->get('referer'));
    }
    //***********************************************************************************************************************************************//
    //********************************************************** Fin: Section user ******************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //********************************************************* Début: Section Etat ******************************************************************//
    //***********************************************************************************************************************************************//

    /**
     * Permet d'afficher la liste de tous les objets "Etat"
     * 
     * @Route("/administration/etat/liste", name="administration_etat_liste")
     *
     * @param EtatRepository $repoEtat
     * 
     * @return Response
     */
    public function listeEtat(EtatRepository $repoEtat): Response
    {
        $etats = $repoEtat->findAll();
    
        return $this->render('administration/etat/liste.html.twig', [
            'etats' => $etats, 
            'titre' => $this->translator->trans('Status')
        ]);
    }

    /**
     * Permet de modifier un objets "Etat"
     * 
     * @Route("/administration/etat/{id}/modifier", name="administration_etat_modifier")
     *
     * @param Etat $etat
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function modifierEtat(Etat $etat, Request $request, EntityManagerInterface $manager): Response
    {
        
        $form = $this->createForm(EtatType::class, $etat);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($etat);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The status has been edited').' !'
            ); 
            
            return $this->redirectToRoute('administration_etat_liste');
        }

        return $this->render('administration/etat/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Edit status') . ' : <strong>' . $etat->getcodeEtat().'</strong>',
            'form' => $form->createView()
        ]);   
    }

    /**
     * Permet de créer un nouvel objets "Etat"
     * 
     * @Route("/administration/etat/ajouter", name="administration_etat_ajouter")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function ajouterEtat(Request $request, EntityManagerInterface $manager): Response
    {
        $etat = new Etat();

        $form = $this->createForm(EtatType::class, $etat);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($etat);
            $manager->flush();         
            
            $this->addFlash(
                'success', 
                $this->translator->trans('The state has been added').' !'
            ); 
            
            return $this->redirectToRoute('administration_etat_liste');
        }

        return $this->render('administration/etat/ajouter-modifier.html.twig', [
            'titre' => $this->translator->trans('Add status'),
            'form' => $form->createView()
        ]);   
    }

    //***********************************************************************************************************************************************//
    //********************************************************** Fin: Section Etat *******************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //********************************************************* Début: Section xxx ******************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
    //********************************************************** Fin: Section xxx *******************************************************************//
    //***********************************************************************************************************************************************//

    //***********************************************************************************************************************************************//
}