<?php

namespace App\Service\Logistique;

use DateTime;
use App\Entity\Colis;
use App\Entity\SuiviColis;
use App\Repository\EtatRepository;
use App\Repository\ColisRepository;
use Doctrine\Persistence\ObjectManager;
use App\Repository\CodePostalRepository;
use App\Repository\SuiviColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogistiqueColisService extends AbstractController
{
    private $requestStack;
    private $manager;
    private $translator;
    private $repoCodePostal;
    private $repoColis;
    private $repoSuiviColis;
    private $repoEtat;
    
    /**
     * Constructeur
     *
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     * @param CodePostalRepository $repoCodePostal
     * @param ColisRepository $repoColis
     * @param SuiviColisRepository $repoSuiviColis
     * @param EtatRepository $repoEtat
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $manager, TranslatorInterface $translator, CodePostalRepository $repoCodePostal, ColisRepository $repoColis, SuiviColisRepository $repoSuiviColis, EtatRepository $repoEtat )
    {
        $this->manager          = $manager;
        $this->translator       = $translator;
        $this->repoCodePostal   = $repoCodePostal;
        $this->repoColis        = $repoColis;
        $this->repoSuiviColis   = $repoSuiviColis;
        $this->repoEtat         = $repoEtat;
        $this->requestStack     = $requestStack;
    }

    /**
     * Permet l'ajout de nouveaux colis dans la DB à partir d'un fichier csv
     *
     * @param File $uploadedFile
     * @param bool $typeColis
     * 
     * @return array
     */
    public function uploadListColis($uploadedFile, $typeColis): array
    {
        // Pour des raisons de performance, on utilise les requêtes sql natives 
        // Il faudra envisager d'archiver ou de supprimer les colis livrés il y a plus d'un mois

        //set_time_limit(480);
        $conn = $this->getDoctrine()->getConnection();

        $conn->getConfiguration()->setSQLLogger(NULL);

        $requetColis = $conn->prepare('INSERT INTO colis (numero_colis, nom_destinataire, prenom_destinataire, adresse_destinataire, numero_adresse_destinataire, code_postal_id, type_colis, express_colis)
                    VALUES (:numero_colis, :nom_destinataire, :prenom_destinataire, :adresse_destinataire, :numero_adresse_destinataire, :code_postal_id, :type_colis, :express_colis)');
        $requeteSuivicolis = $conn->prepare('INSERT INTO suivi_colis (date_suivi_colis, colis_id, etat_id)
                    VALUES (:dateSuiviColis, :colis, :etat)');
        $etatObjet = $this->repoEtat->findOneBy(['codeEtat' => '000']);
        $etat = $etatObjet->getId();

        $tableauErreurs = array();

        //Si php ne détecte pas la fin de ligne 
        //ini_set("auto_detect_line_endings", true);
        //extraire les données du fichier avant l'envoi en base de donnée
        if (($fp = @fopen($uploadedFile, "rt")) !== false)
        {    
            $cpt=0;
            $error = 0;

            while (!feof($fp))
            {   
                $ligne = fgets($fp,4096);
                $liste = explode(";", $ligne); //décompose le contenu de $ligne dans un tableau $liste                    
                $liste[0] = ( isset($liste[0]) ) ? $liste[0] : Null;
                $liste[1] = ( isset($liste[1]) ) ? $liste[1] : Null;
                $liste[2] = ( isset($liste[2]) ) ? $liste[2] : Null;
                $liste[3] = ( isset($liste[3]) ) ? $liste[3] : Null;
                $liste[4] = ( isset($liste[4]) ) ? $liste[4] : Null;
                $liste[5] = ( isset($liste[5]) ) ? $liste[5] : Null;
                $liste[6] = ( isset($liste[6]) ) ? $liste[6] : Null;

                $champs1=$liste[0];
                $champs2=$liste[1];
                $champs3=$liste[2];
                $champs4=$liste[3];
                $champs5=$liste[4];
                $champs6=$liste[5];
                $champs7= str_replace("\n", "",$liste[6]); //supprime le caractère de fin de ligne
                
                $dateObjet = new DateTime();
                $dateString = $dateObjet->format('Y-m-d H:i:s');               

                if ($champs1 != '' && $champs2 != '' && $champs3 != '' && $champs4 != '' && $champs5 != '' )
                {    
                    $cpobjet = $this->repoCodePostal->findOneBy(['numCodePostal' =>  $champs6]);
                    $cp = $cpobjet->getid();

                    // Vérifier si l'objets "CodePostal" existe et l'objet 'Colis' n'exise pas    
                    if (($cpobjet->getNumCodePostal() == $champs6 ) && (!($this->repoColis->findOneBy(['numeroColis' => $champs1]) ) ) )
                    {
                        $requetColis->bindValue('numero_colis', $champs1);
                        $requetColis->bindValue('nom_destinataire', $champs2);
                        $requetColis->bindValue('prenom_destinataire', $champs3);
                        $requetColis->bindValue('adresse_destinataire', $champs4);
                        $requetColis->bindValue('numero_adresse_destinataire', $champs5);
                        $requetColis->bindValue('code_postal_id', $cp);
                        $requetColis->bindValue('type_colis', $typeColis);
                        $requetColis->bindValue('express_colis', $champs7);

                        $requetColis->execute();
                        
                        // Récupère l'id du dernier 'Insert' dans la DB pour créer un 'SuiviCollis' 
                        $id = $conn->lastInsertId();

                        $requeteSuivicolis->bindValue('dateSuiviColis',$dateString);
                        $requeteSuivicolis->bindValue('colis', $id);
                        $requeteSuivicolis->bindValue('etat', $etat);
                       
                        $requeteSuivicolis->execute();
            
                        $cpt++ ;  
                    }  
                    else
                    {
                        $tableauErreurs[] = ['numero' => $champs1, 'nom' => $champs2, 'prenom' => $champs3, 'adresse'=> $champs4, 'num' => $champs5, 'codePostal' => $champs6, 'express' => $champs7] ; 
                        $error++ ; 
                    }            
                }                                 
            }

            fclose($fp); 

            if ($cpt > 0) 
            {
                $this->addFlash(
                'success',
                $cpt. ' '.$this->translator->trans('New parcel(s) are added').' !'
                );
            }
            if ($error > 0)
            {
                $this->addFlash(
                'warning',
                $error. ' '.$this->translator->trans('Parcels has not been added').' !'
                );
            } 
        }
        else
        {
            $this->addFlash(
                'danger',
                $this->translator->trans('File Error : can not open this file').' !'
            );
        } 

        return $tableauErreurs;
    }

    /**
     * Prépare le fichier json pour la requête Ajax du plugin "dataTables" Colis
     *
     * @param bool $typeColis
     * @param string $route
     * 
     * @return string
     */
    public function dataTablesColisApi($typeColis, $route): string
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
            // Converti le numéro de colonne en nom de colonne
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }
        
        // Fait appel à la méthode du SuiviColisRepository correspondante à la route
        switch($route)
        {
            case 'logistique_expedition_liste':
            case 'logistique_enlevement_liste':
                $results = $this->repoSuiviColis->getListeColis($typeColis, $start, $length, $orders, $search); 
                $total_objects_count = $this->repoSuiviColis->listeColisCount($typeColis);
                break; 
            case 'logistique_expedition_nouvelle':
            case 'logistique_enlevement_nouvelle':
                $results = $this->repoSuiviColis->getListeColisDuJour($typeColis, $start, $length, $orders, $search); 
                $total_objects_count = $this->repoSuiviColis->listeColisDuJourCount($typeColis);  
                break; 
            case 'logistique_expedition_en-cours':
            case 'logistique_enlevement_en-cours':
                $results = $this->getListeColisEnCours($typeColis, $start, $length, $orders, $search);
                $total_objects_count =  $results ['total_objects_count'];
                break;
            case 'logistique_expedition_livre':
            case 'logistique_enlevement_livre':
                $results = $this->repoSuiviColis->getListeColisFerme($typeColis, $start, $length, $orders, $search);  
                $total_objects_count = $this->repoSuiviColis->listeColisFermeCount($typeColis); 
                break;  
            case 'logistique_expedition_litige':
            case 'logistique_enlevement_litige':
                $results = $this->getListeColisLitige($typeColis, $start, $length, $orders, $search); 
                $total_objects_count =  $results ['total_objects_count'];
                break;
            case 'logistique_express':
                $results = $this->repoSuiviColis->getListeColisExpressDuJour($typeColis, $start, $length, $orders, $search); 
                $total_objects_count = $this->repoSuiviColis->listeColisExpressDuJourCount($typeColis);
                break; 
            case 'logistique_orphelin';
                $results = $this->repoSuiviColis->getListeColisOrphelin($typeColis, $start, $length, $orders, $search); 
                $total_objects_count = $this->repoSuiviColis->listeColisOrphelinCount($typeColis);  
                break;   
            default;
                die;   
        } 

        $objects = $results["results"];
        // Nombre total de $resultat
        $selected_objects_count = count($objects);
        // Nombre d'objets après les filtrage
        $filtered_objects_count = $results["countResult"];

        $response = '{
            "draw": '.$draw.',
            "recordsTotal": '.$total_objects_count.',
            "recordsFiltered": '.$filtered_objects_count.',
            "data": [';
            
        $i = 0;
        foreach ($objects as $key => $suiviColis)
        {
            // Construit la réponse 'json'
            $response .= '["';

            $j = 0; 
            $nbColumn = count($columns);
            foreach ($columns as $key => $column)
            {
                // Si soucis ou valeur null, on returne "-"
                $responseTemp = "-";
    
                switch($column['name'])
                {
                    case 'Parcel number':
                    {
                        $responseTemp = $suiviColis->getColis()->getNumeroColis();
                        break; 
                    }
                    case 'Last name':
                    {
                        $responseTemp = $suiviColis->getColis()->getNomDestinataire();
                        break; 
                    }
                    case 'First name':
                    {
                        $responseTemp = $suiviColis->getColis()->getPrenomDestinataire();
                        break; 
                    }
                    case 'Address':
                    {
                        $responseTemp = $suiviColis->getColis()->getAdresseDestinataire();
                        break; 
                    }
                    case 'Number':
                    {
                        $responseTemp = $suiviColis->getColis()->getNumeroAdresseDestinataire();
                        break; 
                    }
                    case 'Postal code':
                    {
                        $responseTemp = $suiviColis->getColis()->getcodePostal()->getNumCodePostal();
                        break; 
                    }
                    case 'Action':
                    {
                        $id = $suiviColis->getColis()->getId();
                        $urlExpeditionDetail = $this->generateUrl('logistique_expedition_details', array('id' => $id));
                        $urlExpeditionModifier = $this->generateUrl('logistique_expedition_modifier', array('id' => $id));
                        $urlEnlevementDetail = $this->generateUrl('logistique_enlevement_details', array('id' => $id));
                        $urlEnlevementModifier = $this->generateUrl('logistique_enlevement_modifier', array('id' => $id));

                        if ($typeColis)
                        {
                            $responseTemp = "<a href='".$urlExpeditionDetail."' data-toggle='tooltip' data-placement='bottom' title='".$this->translator->trans('See details')."' class='btn btn-dark ml-auto'><i class='fas fa-eye'></i></a> <a href='".$urlExpeditionModifier."' data-toggle='tooltip' data-placement='bottom' title='".$this->translator->trans('Edit delivery')."' class='btn btn-warning'><i class='fas fa-edit'></i></a>";  
                        }                
                        else
                        {
                            $responseTemp = "<a href='".$urlEnlevementDetail."' data-toggle='tooltip' data-placement='bottom' title='".$this->translator->trans('See details')."' class='btn btn-dark ml-auto'><i class='fas fa-eye'></i></a> <a href='".$urlEnlevementModifier."' data-toggle='tooltip' data-placement='bottom' title='".$this->translator->trans('Edit pickup')."' class='btn btn-warning'><i class='fas fa-edit'></i></a>";        
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

    /**
     * Retourne un tableau d'objets "Suiviscolis" des objets "Colis" ouvert, filtré trieé selon l'appel Ajax de dataTables 
     *
     * @param bool  $typeColis
     * @param int   $start
     * @param int   $length
     * @param array $orders
     * @param array $search
     * 
     * @return array
     */
    public function getListeColisEnCours($typeColis, $start, $length, $orders, $search): array
    {
        $tableauColisNonFiltre = array();
        $colisFiltre = array();
        // On récupère tous les objet "Colis"
        $tableauColisNonFiltre = $this->repoColis->findBy(['typeColis' => $typeColis ] );

        foreach ($tableauColisNonFiltre as $colis)
        {
            // Pour chaque suiviColis du tableau $colis->getSuiviColis() on récupere le suiviColis
            $tableauEtatParColis = [];
            foreach ($colis->getSuiviColis() as $suivis)
            {
                // On ajoute le codeEtat dans un tableau 
                $tableauEtatParColis[] = $suivis->getEtat()->getCodeEtat();
            } 
            // On vérifie si l'obet "Colis" a été livré (999)
            if (! (in_array("999", $tableauEtatParColis)))
            {
                $colisFiltre [] = clone $colis;       
            } 
        }

        return $this->parametreDataTableAjax($colisFiltre, $start, $length, $orders, $search);
    }

    /**
     * Retourne un tableau d'objets "Suiviscolis" des objets "Colis" en litige, filtré trieé selon l'appel Ajax de dataTables 
     *
     * @param bool  $typeColis
     * @param int   $start
     * @param int   $length
     * @param array $orders
     * @param array $search
     * 
     * @return array
     */
    public function getListeColisLitige($typeColis, $start, $length, $orders, $search): array
    { 
        $tableauColisNonFiltre = array();
        $colisFiltre = array();
        //On récupère tous les objets "SuiviColis" ayant le codeEtat '008'
        $suiviColis =  $this->manager->createQuery(
                            "   SELECT s
                                FROM App\Entity\SuiviColis s
                                JOIN s.etat e
                                JOIN s.colis c
                                JOIN c.codePostal p
                                WHERE e.codeEtat = '008'
                                AND c.typeColis = $typeColis
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

        return $this->parametreDataTableAjax($colisFiltre, $start, $length, $orders, $search);
    }

    /**
     * Effectue le tri sur un champ et le filtrage sur le critère de recherche ainsi que les differents comptages
     *
     * @param array $colisFiltre
     * @param int   $start
     * @param int   $length
     * @param array $orders
     * @param array $search
     * 
     * @return array
     */
    public function parametreDataTableAjax($colisFiltre, $start, $length, $orders, $search): array
    {
        // Recupère le nombre totals d'enregistrement avant de filtrer sur le critère de recherche
        $total_objects_count = count($colisFiltre);

        // Filtre sur le critère de recherche (minimum 2 caractères)
        if ($search['value'] != '' && strlen($search['value']) >= 2 )
        {   
            // $searchItem : terme de la recherche 
            // Supprime les espaces en début et en fin de chaîne
            $searchItem = trim($search['value']) ;
            
            foreach ($colisFiltre as $colis)
            {
                // Cherhche une occurence du critère de recherche '$searchItem' dans chaques champs de l'objet "Colis"
                if (stristr($colis->getNumeroColis(), $searchItem) || stristr($colis->getNomDestinataire(), $searchItem) || stristr($colis->getPrenomDestinataire(), $searchItem) || stristr($colis->getAdresseDestinataire(), $searchItem) || stristr($colis->getNumeroAdresseDestinataire(), $searchItem) || stristr($colis->getCodePostal()->getNumCodePostal(), $searchItem) );
                else unset($colisFiltre[array_search($colis, $colisFiltre)]);
            }
        }
        
        /**
         * On récupère 1 objet "SuiviColis" de chaques "colisFiltre" afin d'envoyer à la vue "liste-html.twig" un tableau d'objets "SuiviColis". 
         * Sinon il faut prévoire une autre vue qui attendra un tableau d'objets "Colis"
         */ 
        $suiviColis = array();

        foreach ($colisFiltre as $colis)
        {
            $suiviColis[] =  $this->repoSuiviColis->findOneBy(['colis' => $colis]); 
        } 
        // Tri des objets "SuiviColis"
        foreach ($orders as $key => $order)
        {
            $direction = $order['dir'];
            // $order['name'] : le nom de la colonne à trier 
            if ($order['name'] != '')
            {
                switch($order['name'])
                {
                    case 'Parcel number':
                    {
                        if($direction == 'asc')
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getNumeroColis();
                                $b = $objb->getColis()->getNumeroColis();
                                if ($a == $b) return 0;
                                else return ($a < $b) ? -1 : 1;
                            } );
                        }
                        else 
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getNumeroColis();
                                $b = $objb->getColis()->getNumeroColis();
                                if ($a == $b) return 0;
                                else return ($a > $b) ? -1 : 1;
                            } ); 
                        }   
                        break;
                    }
                    case 'Last name':
                    {   if($direction == 'asc')
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getNomDestinataire();
                                $b = $objb->getColis()->getNomDestinataire(); 
                                if ($a == $b) return 0;
                                else return ($a < $b) ? -1 : 1;
                            } ); 
                        }
                        else
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getNomDestinataire();
                                $b = $objb->getColis()->getNomDestinataire();
                                if ($a == $b) return 0;
                                else return ($a > $b) ? -1 : 1;
                            } );
                        }    
                        break;
                    }
                    case 'First name':
                    {
                        if($direction == 'asc')
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getPrenomDestinataire();
                                $b = $objb->getColis()->getPrenomDestinataire();
                                if ($a == $b) return 0;
                                else return ($a < $b) ? -1 : 1;
                            } ); 
                        }
                        else
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getPrenomDestinataire();
                                $b = $objb->getColis()->getPrenomDestinataire();
                                if ($a == $b) return 0;
                                else return ($a > $b) ? -1 : 1;
                            } );
                        }    
                        break;
                    }
                    case 'Address':
                    {
                        if($direction == 'asc')
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getAdresseDestinataire();
                                $b = $objb->getColis()->getAdresseDestinataire();
                                if ($a == $b) return 0;
                                else return ($a < $b) ? -1 : 1;
                            } ); 
                        }
                        else
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getAdresseDestinataire();
                                $b = $objb->getColis()->getAdresseDestinataire();
                                if ($a == $b) return 0;
                                else return ($a > $b) ? -1 : 1;
                            } );
                        }   
                        break;
                    }
                    case 'Number':
                    {
                        if($direction == 'asc')
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getNumeroAdresseDestinataire();
                                $b = $objb->getColis()->getNumeroAdresseDestinataire();
                                if ($a == $b) return 0;
                                else return ($a < $b) ? -1 : 1;
                            } ); 
                        }
                        else
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getNumeroAdresseDestinataire();
                                $b = $objb->getColis()->getNumeroAdresseDestinataire();
                                if ($a == $b) return 0;
                                else return ($a > $b) ? -1 : 1;
                            } );
                        }    
                        break;
                    }
                    case 'Postal code':
                    {
                        if($direction == 'asc')
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getCodePostal()->getNumCodePostal();
                                $b = $objb->getColis()->getCodePostal()->getNumCodePostal();
                                if ($a == $b) return 0;
                                else return ($a < $b) ? -1 : 1;
                            } ); 
                        }
                        else
                        {
                            usort($suiviColis, function($obja, $objb){
                                $a = $obja->getColis()->getCodePostal()->getNumCodePostal();
                                $b = $objb->getColis()->getCodePostal()->getNumCodePostal();
                                if ($a == $b) return 0;
                                else return ($a > $b) ? -1 : 1;
                            } );
                        }    
                        break;
                    }
                }
            }
        }
        
        $countResult =  count($suiviColis);
        // Limite et Offset  
        $results = array_slice($suiviColis, $start, $length);
        
        return array(
            "results" 		=> $results,
            "countResult"	=> $countResult,
            'total_objects_count' => $total_objects_count
        );
    }
}