<?php

namespace App\Repository; 
 
use DateTime;
use Doctrine\ORM\Query;
use App\Entity\SuiviColis;
use Doctrine\ORM\QueryBuilder;

use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method SuiviColis|null find($id, $lockMode = null, $lockVersion = null)
 * @method SuiviColis|null findOneBy(array $criteria, array $orderBy = null)
 * @method SuiviColis[]    findAll()
 * @method SuiviColis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuiviColisRepository extends  ServiceEntityRepository
{
    private $manager;

    /**
     * Constructeur
     *
     * @param ManagerRegistry $registry
     * @param EntityManagerInterface $manager
     */
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager)
    {
        parent::__construct($registry, SuiviColis::class);
        
        $this->manager = $manager;
    }
    
    /**
     * Retourne un tableau d'objets "Suiviscolis" de tous objets "Colis" selon "typeColis", filtré, trié selon l'appel Ajax de dataTables 
     *
     * @param bool $typeColis
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search
     *
     * @return array
     */
    public function getListeColis($typeColis, $start, $length, $orders, $search): array
    {    
        return $this->getColis($typeColis, $start, $length, $orders, $search);
    }

    /**
     * Retourne le nombre total d'objets "SuivisColis" selon le "typeColis" non filtré 
     *
     * @param bool $typeColis
     * 
     * @return int
     */
    public function listeColisCount($typeColis): int
    {
        $dql = $this->createQueryBuilder('s') 
                    ->select('s')
                    ->leftJoin('s.colis', 'c')
                    ->leftJoin('c.codePostal', 'p')
                    ->where('c.typeColis = :val')
                    ->setParameter('val', $typeColis)
                    ->groupBy('s.colis')
                    ->getQuery()
                    ->getResult()
        ; 
        return count($dql); 
    }

    /**
     * Retourne un tableau d'objets "Suiviscolis" des objets "Colis" du jour selon "typeColis", filtré, trieé selon l'appel Ajax de dataTables 
     *
     * @param bool $typeColis
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search 
     * 
     * @return array
     */
    public function getListeColisDuJour($typeColis, $start, $length, $orders, $search): array
    {
        $dateToday = new DateTime('today');
        list($year,$month,$day) = explode("/", $dateToday->format('Y/m/d'));
         
        $where = 
            [
                's.dateSuiviColis >= :date',
                'e.codeEtat = :codeEtat'
            ];
        $parameter = 
            [
                'date' => "$year-$month-$day",
                'codeEtat' => 000
            ];    
            
        $otherConditions = ['where' => $where , 'parameter' => $parameter]  ; 
            
        return $this->getColis($typeColis, $start, $length, $orders, $search, $otherConditions);
    }

    /**
     * Retourne le nombre total d'objets "SuivisColis" du jour selon le "typeColis" non filtré
     *
     * @param bool $typeColis
     * 
     * @return int
     */
    public function listeColisDuJourCount($typeColis): int
    {
        $dateToday = new DateTime('today');
        list($year,$month,$day) = explode("/", $dateToday->format('Y/m/d'));
       
        $dql = $this->createQueryBuilder('s') 
                    ->select('s')
                    ->leftJoin('s.colis', 'c')
                    ->leftJoin('c.codePostal', 'p')
                    ->leftJoin('s.etat', 'e')
                    ->where('c.typeColis = :typeColis')
                    ->setParameter('typeColis', $typeColis)
                    ->andWhere('s.dateSuiviColis >= :date')
                    ->setParameter('date',"$year-$month-$day")
                    ->andWhere('e.codeEtat = :codeEtat')
                    ->setParameter('codeEtat', 000)
                    ->groupBy('s.colis')
                    ->getQuery()
                    ->getResult()
        ; 
        return count($dql);  
    }

    /**
     * Retourne un tableau d'objets "Suiviscolis" des objets "Colis" express du jour, filtré, trié selon l'appel Ajax de dataTables 
     *
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search 
     * 
     * @return array
     */
    public function getListeColisExpressDuJour($typeColis, $start, $length, $orders, $search): array
    {
        $dateToday = new DateTime('today');
        list($year,$month,$day) = explode("/", $dateToday->format('Y/m/d'));
         
        $where = 
            [
                's.dateSuiviColis >= :date',
                'e.codeEtat = :codeEtat',
                'c.expressColis = :express ' 
            ];
        $parameter = 
            [
                'date' => "$year-$month-$day",
                'codeEtat' => 000,
                'express' => 1
            ];    
            
        $otherConditions = ['where' => $where , 'parameter' => $parameter]  ; 
            
        return $this->getColis($typeColis, $start, $length, $orders, $search, $otherConditions);
    }

    /**
     * Retourne le nombre total d'objets "SuivisColis" du jour selon le critère "expressColis" non filtrés
     * 
     * @return int
     */
    public function listeColisExpressDuJourCount($typeColis): int
    {
        $dateToday = new DateTime('today');
        list($year,$month,$day) = explode("/", $dateToday->format('Y/m/d'));
       
        $dql = $this->createQueryBuilder('s') 
                    ->select('s')
                    ->leftJoin('s.colis', 'c')
                    ->leftJoin('c.codePostal', 'p')
                    ->leftJoin('s.etat', 'e')
                    ->where('c.typeColis = :typeColis')
                    ->setParameter('typeColis', $typeColis)
                    ->andWhere('c.expressColis = :express')
                    ->setParameter('express', 1)
                    ->andWhere('s.dateSuiviColis >= :date')
                    ->setParameter('date',"$year-$month-$day")
                    ->andWhere('e.codeEtat = :codeEtat')
                    ->setParameter('codeEtat', 000)
                    ->groupBy('s.colis')
                    ->getQuery()
                    ->getResult()
        ; 
        return count($dql);  
    }

    /**
     * Retourne un tableau d'objets "Suiviscolis" des objets "Colis" fermé selon "typeColis", filtré, trieé selon l'appel Ajax de dataTables 
     *
     * @param bool $typeColis
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search
     * 
     * @return array
     */
    public function getListeColisFerme($typeColis, $start, $length, $orders, $search): array
    { 
        $where = ['e.codeEtat = :codeEtat' ];
        $parameter = ['codeEtat' => 999];   

        $otherConditions = ['where' => $where , 'parameter' => $parameter]  ; 
            
        return $this->getColis($typeColis, $start, $length, $orders, $search, $otherConditions);
    }

    /**
     * Retourne le nombre total d'objets "SuivisColis" fermés selon le "typeColis" non filtré
     *
     * @param bool $typeColis
     * 
     * @return int
     */
    public function listeColisFermeCount($typeColis): int
    {
        $dql = $this->createQueryBuilder('s') 
                    ->select('s')
                    ->leftJoin('s.colis', 'c')
                    ->leftJoin('c.codePostal', 'p' )
                    ->where('c.typeColis = :val')
                    ->setParameter('val', $typeColis)
                    ->leftJoin('s.etat', 'e')
                    ->where('e.codeEtat = :val')
                    ->setParameter('val', 999)
                    ->groupBy('s.colis')                     
                    ->getQuery()
                    ->getResult()
        ; 
        return count($dql);  
    }

    /**
     * Prépare les données pour la requête Ajax du plugin "dataTables", retourne un tableau de "SuiviColis"
     *
     * @param bool $typeColis
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search
     * @param array $columns
     *
     * @return array
     */
    public function getColis($typeColis, $start, $length, $orders, $search, $otherConditions = null): array
    {
        // Requête principale
        $query = $this->createQueryBuilder('s');
        // Requête pour le nombre d'enregistrement
        $countQuery = $this->createQueryBuilder('s');
        $countQuery->select('s');            
        
        // clause join
        $query      ->leftJoin('s.colis', 'c')
                    ->leftJoin('c.codePostal', 'p')
                    ->leftJoin('s.etat', 'e');
        $countQuery ->leftJoin('s.colis', 'c')
                    ->leftJoin('c.codePostal', 'p')
                    ->leftJoin('s.etat', 'e');

        // clause where
        $query      ->where("c.typeColis = $typeColis");      
        $countQuery ->where("c.typeColis = $typeColis"); 
               
        if ($otherConditions != null)
        {
            foreach ($otherConditions['where'] as $whereCondition)
            {
                $query->andWhere($whereCondition);
                $countQuery->andWhere($whereCondition); 
            }
            foreach ($otherConditions['parameter'] as $key => $parameter)
            {
                $query->setParameter($key , $parameter );
                $countQuery->setParameter($key , $parameter); 
            }
        }      
         // clause groupBy
         $query      ->groupBy('s.colis');
         $countQuery ->groupBy('s.colis');
        
         // Filtre sur le critère de recherche (minimum 2)
        if ($search['value'] != ''  && strlen($search['value']) >= 2)
        {
            // $searchItem : terme de la recherche 
            // Supprime les espaces en début et en fin de chaîne
            $searchItem = trim($search['value']) ;
            $tabSearchQuery = null;
            $tabSearchQuery =   [ 
                                    'c.numeroColis' => ':val',
                                    'c.nomDestinataire' => ':val',
                                    'c.prenomDestinataire' => ':val',
                                    'c.adresseDestinataire' => ':val',
                                    'c.numeroAdresseDestinataire' => ':val',
                                    'p.numCodePostal' => ':val',
                                ];

            $queryOrStatements = $query->expr()->orX();
            $countQueryOrStatements = $countQuery->expr()->orX();

            foreach ($tabSearchQuery as $key => $value)
            {
                $queryOrStatements->add(
                    $query->expr()->like($key, $value)
                );
                $countQueryOrStatements->add(
                    $countQuery->expr()->like($key,$value)
                );         
            }  
            $query->andWhere($queryOrStatements)->setParameter('val', '%'.$searchItem.'%');
            $countQuery->andWhere($countQueryOrStatements)->setParameter('val', '%'.$searchItem.'%');  
        }
        // Limite et Offset      
        $query->setFirstResult($start)->setMaxResults($length);    
        
        // Tri
        foreach ($orders as $key => $order)
        {
            // $order['name'] : le nom de la colonne à trier
            if ($order['name'] != '')
            {
                $orderColumn = null;
            
                switch($order['name'])
                {
                    case 'Parcel number':
                    {
                        $orderColumn = 'c.numeroColis';
                        break;
                    }
                    case 'Last name':
                    {
                        $orderColumn = 'c.nomDestinataire';
                        break;
                    }
                    case 'First name':
                    {
                        $orderColumn = 'c.prenomDestinataire';
                        break;
                    }
                    case 'Address':
                    {
                        $orderColumn = 'c.adresseDestinataire';
                        break;
                    }
                    case 'Number':
                    {
                        $orderColumn = 'c.numeroAdresseDestinataire';
                        break;
                    }
                    case 'Postal code':
                    {
                        $orderColumn = 'p.numCodePostal';
                        break;
                    }
                }

                if ($orderColumn !== null)
                {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }   
        // Execute la requête
        $results = $query->getQuery()->getResult();
        $countResult = count($countQuery->getQuery()->getResult()) ;
        
        return array(
            "results" 		=> $results,
            "countResult"	=> $countResult
        );
    }
}
