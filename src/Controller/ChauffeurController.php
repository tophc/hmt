<?php

namespace App\Controller;

use DateTime;
use App\Entity\Requete;
use App\Entity\Chauffeur;
use App\Form\ContactType;
use App\Entity\SuiviColis;
use App\Repository\EtatRepository;
use App\Repository\ColisRepository;
use App\Repository\AmendeRepository;
use App\Repository\RequeteRepository;
use App\Service\Notification\Contact;
use App\Service\TraductionDateService;
use App\Service\TraductionEtatService;
use App\Repository\SuiviColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AffectationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Chauffeur\ChauffeurAmendeService;
use App\Service\Notification\ContactNotification;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Chauffeur\ChauffeurStatistiqueService;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
 

class ChauffeurController extends AbstractController 
{
    private $translator;

    /**
     * Constructeur
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Permet d'afficher le "Dashboard" "Chauffeur"
     * 
     * @Route("/chauffeur/dashboard", name="chauffeur_dashboard")
     * 
     * @param ChauffeurStatistiqueService $chauffeurStatistiqueService
     * 
     * @return Response
     */
    public function dashboard(ChauffeurStatistiqueService $chauffeurStatistiqueService ): Response
    {   
        // Si l'utilisateur a le rôle "ROLE_NEW_USER" on le redirige vers la page de modification du mot de passe
        $user = $this->getUser();
        if (in_array("ROLE_NEW_USER", $user->getRoles()))
        {
            $this->addFlash(
                'danger',
                $this->translator->trans('Password has to be changed') 
            );

            return $this->redirectToRoute("chauffeur_password");
        } 
        
        //On fait appel au service de statistique "ChauffeurStatistiqueService"
        $statistiques = $chauffeurStatistiqueService->getStatistique($user);
     
        return $this->render('chauffeur/dashboard.html.twig', [
            'titre' => $this->translator->trans('Dashboard'),
            'chauffeur' => $this->getUser(),
            'statistiques' => $statistiques,
        ]);
    }
    /**
     * Ajoute un objet "SuiviColis" via une requête ajax (dayat 2.0)
     * 
     * @Route("/chauffeur/dayat/ajax/etat", name="chauffeur_dayat_ajax_etat")
     *
     * @param Request $request
     * @param ColisRepository $repoColis
     * @param EtatRepository $repoEtat
     * @param SuiviColisRepository $repoSuiviColis 
     * @param TraductionEtatService $traductionEtatService
     * @param EntityManagerInterface $manager
     * 
     * @return JsonResponse
     */
    public function ajaxDayatAjouterEtat(Request $request, ColisRepository $repoColis, EtatRepository $repoEtat, SuiviColisRepository $repoSuiviColis, TraductionEtatService $traductionEtatService, EntityManagerInterface $manager): JsonResponse
    {
        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) 
        {
            $user = $this->getuser();

            $numeroColis = $request->request->get('numColis');
            $colis = $repoColis->findOneBy(['numeroColis' => $numeroColis]);
            
            $codeEtat = $request->request->get('codeEtat');
            $etat = $repoEtat->findOneBy(['codeEtat' => $codeEtat]);
            
            $suiviColis = $repoSuiviColis->findOneBy(['colis' => $colis, 'etat' => $etat]);

            if (! $suiviColis )
            {
                $suiviColis = new SuiviColis();
                $suiviColis->setDateSuiviColis(new dateTime())
                           ->setColis($colis)
                           ->setEtat($etat);
                
                $colis->setNoteColis($colis->getNoteColis().'<br> -  '  .$user->getNomchauffeur().' '.$user->getPrenomChauffeur().' ('.date('d.m.Y H:m').') : <strong>Add : </strong>'.$suiviColis->getEtat()->getCodeEtat());
                 
                $manager->persist($suiviColis);
                $manager->flush();
                
                $response = ['message' => 'Etat '.$suiviColis->getEtat()->getCodeEtat().' ajouté'];
            }
            else
            {
                $manager->remove($suiviColis);
                
                $colis->setNoteColis($colis->getNoteColis().'<br> -  '  .$user->getNomchauffeur().' '.$user->getPrenomChauffeur().' ('.date('d.m.Y H:m').') : <strong>Remove : </strong>'.$suiviColis->getEtat()->getCodeEtat()); 
                
                $manager->persist($colis);
                $manager->flush();

                $response = ['message' => 'Etat '.$suiviColis->getEtat()->getCodeEtat().' supprimé'];
            }

            $response[] = 200;
            
            return $this->json($response);
        }
    }        

    /**
     * Renvoie les données à afficher dans le "Dayat 2.0" dans un fichier json
     * 
     * @Route("/chauffeur/dayat/ajax", name="chauffeur_dayat_ajax_track")
     * 
     * @param Request $request 
     * @param ColisRepository $repoColis
     * @param EtatRepository $repoEtat
     * @param TraductionEtatService $traductionEtatService
     * 
     * @return JsonResponse
     */
    public function ajaxDayat(Request $request, ColisRepository $repoColis, EtatRepository $repoEtat, TraductionEtatService $traductionEtatService): JsonResponse
    {
        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) 
        {
            $user = $this->getuser();
            $numeroColis = $request->request->get('numColis');
            $colis = $repoColis->findOneBy(['numeroColis' => $numeroColis]);
            $etats = $repoEtat->findAll();
            if ($colis)
            {
                $response = [
                    'info' => $numeroColis,
                    'donnee' =>   [
                        $colis->getNomDestinataire().' '.$colis->getPrenomDestinataire(),  
                        $colis->getAdresseDestinataire().', '.$colis->getNumeroAdresseDestinataire(),
                        $colis->getCodePostal()->getNumCodePostal(). ' '.$colis->getCodePostal()->getLocaliteCodePostal()
                    ],
                    'typeColis' => $colis->getTypeColis(),
                    'expressColis' => $colis->getExpressColis(),
                ];             
                
                $length = count($colis->getSuiviColis());
                $i = 0;
                $tableauSuiviColis = array();
                
                foreach ($colis->getSuiviColis() as $suiviColis)
                { 
                    $tableauSuiviColis[] = [
                        ($suiviColis->getDateSuiviColis())->format('d.m.Y'), 
                        $suiviColis->getEtat()->getCodeEtat(), 
                                                    
                    ];  
                } 
                $response['suiviColis'] = $tableauSuiviColis;

                foreach ($etats as $etat)
                { 
                    $tableauCodeEtat[] = [
                        $etat->getCodeEtat(), 
                        $descriptifEtat = $traductionEtatService->traductionDescriptifEtat($etat->getCodeEtat()) 
                    ];                             
                } 

                $response['codeEtat'] = $tableauCodeEtat;
                $response[] = 200;
            }
            else
            {
                $response =['info'  => 0, 'message' => $this->translator->trans('No data') ,404];    
            }
            
            return $this->json($response);
        }
        else
        {
            return $this->redirectToRoute("chauffeur_dayat");
        }
    }

    /**
     * Permet d'afficher la "simultation" d'un "Dayat"
     *
     * @Route("/chauffeur/dayat", name="chauffeur_dayat")
     * 
     * @return Response
     */
    public function dayat(): Response
    {
        return $this->render('chauffeur/dayat/ecran1.html.twig', [
            'titre' => $this->translator->trans('Dayat 2.0'),
            'chauffeur' => $this->getUser(),
        ]);
    }

    /**
     * Permet d'afficher le profile "Chauffeur"
     * 
     * @Route("/chauffeur/profile", name="chauffeur_profile")
     * 
     * @param ChauffeurAmendeService $chauffeurAmendeService
     * 
     * @return Response
     */
    public function profile(ChauffeurAmendeService $chauffeurAmendeService): Response
    {
        $chauffeur = $this->getUser();
        $tableauAmendes = $chauffeurAmendeService->getAmendeByChauffeur($chauffeur, new dateTime('today') );
        
        return $this->render('chauffeur/profile.html.twig', [
            'titre' => $this->translator->trans('Profile'),
            'chauffeur' => $chauffeur,
            'amendes' =>  $tableauAmendes
        ]);
    }
    
    /**
     * Permet d'affiche la liste de toutes les objets "Requete" de l'objet "Chauffeur sous forme de tableau
     * 
     * @Route("/chauffeur/requete/liste/{statut<ouvert|ferme>?}/{service<secretariat|logistique>?}", name="chauffeur_requete_liste")
     * 
     * @param string $statut|null
     * @param string $service|null
     * @param RequeteRepository $repoRequete
     * 
     * @return Response
     */
    public function listeRequete($statut, $service, RequeteRepository $repoRequete): Response
    {
        $chauffeur = $this->getUser();

        if ($statut === 'ouvert') 
        {
            if ($service === 'secretariat') $requetes = $repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'secretariat', 'chauffeur' => $chauffeur] );
            else if ($service === 'logistique') $requetes = $repoRequete->findBy(['statutRequete' => 1 , 'serviceRequete' => 'logistique', 'chauffeur' => $chauffeur] );
            else $requetes = $repoRequete->findBy(['statutRequete' => 1 , 'chauffeur' => $chauffeur] );  
        }
        elseif ($statut === 'ferme')
        {
            if ($service === 'secretariat') $requetes = $repoRequete->findBy(['statutRequete' => 0 , 'serviceRequete' => 'secretariat', 'chauffeur' => $chauffeur] );
            else if ($service === 'logistique') $requetes = $repoRequete->findBy(['statutRequete' => 0 , 'serviceRequete' => 'logistique', 'chauffeur' => $chauffeur] );
            else $requetes = $repoRequete->findBy(['statutRequete' => 0 , 'chauffeur' => $chauffeur] );  
        }
        else 
        {
            if ($service === 'secretariat') $requetes = $repoRequete->findBy(['serviceRequete' => 'secretariat', 'chauffeur' => $chauffeur] );
            else if ($service === 'logistique') $requetes = $repoRequete->findBy(['serviceRequete' => 'logistique', 'chauffeur' => $chauffeur] );
            else $requetes = $chauffeur->getRequetes();
        }
         
        return $this->render('chauffeur/requete/liste.html.twig', [
            'titre' => $this->translator->trans('Requests list'),
            'requetes' => $requetes
        ]);
    }

    /** 
     * Permet de soumetre une "Requete" à un service (secrétariat ou logistique)
     * 
     * @Route("/chauffeur/requete/{service}", name="chauffeur_requete")
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
        $contact->setNom($user->getNomChauffeur());
        $contact->setPrenom($user->getPrenomChauffeur());
        $contact->setEmail($user->getEmailChauffeur());
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
                    $uploadedFile->move('../uploads/chauffeur/'.$user->getId().'/', $newFilename );    
                } catch (FileException $e) 
                {
                    // ... handle exception if something happens during file upload
                }

                $contact->setNomFichier($newFilename, $user->getId());
            }

            $contactNotification->sendEmail($contact , $user->getId());

            $this->addFlash(
                'success',
                $this->translator->trans('The message has been sent successfully') 
            );

            //persiste le mail dans requete
            $requete = new Requete;
            $requete->setMessageRequete($contact->getMessage()) 
                    ->setObjetRequete($contact->getSujet())
                    ->setChauffeur($user)
                    ->setServiceRequete($contact->getService())
                    ->setRequerantRequete('chauffeur');;

            if ($uploadedFile) 
            {
                $requete->setFichierUrlRequete('../uploads/chauffeur/'.$user->getId().'/'.$newFilename);
            }  
           
            $manager->persist($requete);
            $manager->flush(); 
            
           return $this->redirectToRoute("chauffeur_dashboard");
        }
        
        if ($service === 'logistique') $titre = $this->translator->trans('Contact logistics');
        else $titre = $this->translator->trans('Contact secretariat');

        return $this->render('chauffeur/requete/contact.html.twig', [
            'titre' => $titre,
            'form' => $form->createView(),
            'user' => $user,
            'service' => $service
        ]);
    }   

    /**
     *  Permet de télécharger le fichier envoyer lors d'une requête
     * 
     * @Route("/chauffeur/fichier/{id}", name="chauffeur_requete_fichier")
     * 
     * @param Requete $requete
     * 
     * @return Response
     */
    public function telechargerFichierRequete(Requete $requete): Response
    {
        if ($requete->getChauffeur() == $this->getUser())
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
        }
        else 
        {
            $this->addFlash(
                'danger',
                $this->translator->trans('You are not authorised to download this file').' !'
            );
        }

        return $this->redirectToRoute('chauffeur_requete_liste');
    }
     
    /**
     * Affiche le planning du chauffeur
     * 
     * @Route("/chauffeur/planning", name="chauffeur_planning" )
     * 
     * @param AffectationRepository $repoAffectation
     * @param TranslatorInterface $translator
     * 
     * @return Response
     */
    public function affectationFuture( AffectationRepository $repoAffectation, TranslatorInterface $translator): Response
    {
        $date = new DateTime('today');
        $chauffeur = $this->getUser();
        $affectations = $repoAffectation->findByDateFuturChauffeur($chauffeur );
        
        return $this->render('chauffeur/calendrier/affectation.html.twig', [    
        'titre' => $translator->trans('My planning'),
        'chauffeur' => $this->getUser(),
        'affectations' => $affectations
        ]);
    } 

    /**
     * Permet d'afficher la liste de tous les objets 'Amende' du chauffeur sous forme de tableau eventuelement filtré sur le mois et l'année
     * 
     * @Route("/chauffeur/amende/{month<[0-9]{2}>?}/{year<[0-9]{4}>?}", name="chauffeur_amende")
     * 
     * @param string $month|null
     * @param string $year|null
     * @param string $mois
     * @param ChauffeurAmendeService $chauffeurAmendeService
     * 
     * @return Response
     */
    public function listeAmende($month = null, $year = null, ChauffeurAmendeService $chauffeurAmendeService, TraductionDateService $taductionDateService): Response
    {
        $chauffeur = $this->getUser();
        $mois = null;
        
        if ($month && $year)
        {
            $mois = $taductionDateService->traductionDateMoisLong($month);
        } 
        
        $tableauAmendes = $chauffeurAmendeService->getAmendeByChauffeur($chauffeur, $month, $year);

        return $this->render('chauffeur/amende/liste.html.twig', [
            //'titre' => $this->translator->trans('Fines list') ,  
            'titre' => $mois ?  $this->translator->trans('Fines list').' : '.$mois : $this->translator->trans('Fines list'),
            'amendes' => $tableauAmendes
        ]);
    }

    /**
     * Affiche la page d'aide chauffeur
     * 
     * @Route("/chauffeur/help", name="chauffeur_help")
     *  
     * @param TranslatorInterface $translator
     * 
     * @return Response
     */
    public function helpChauffeur(TranslatorInterface $translator): Response
    {
        return $this->render('chauffeur/help.html.twig',[
            'titre' => $translator->trans('Drivers help')
            ]
        );
    }
}

 