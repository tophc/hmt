<?php

namespace App\Repository;

use DateTime;
use DateInterval;
use Doctrine\ORM\Query;
use App\Entity\Vehicule;
use App\Entity\Affectation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Affectation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Affectation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Affectation[]    findAll()
 * @method Affectation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffectationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Affectation::class);  
    }

    /**
     * Retourne un tableau d'objets "SAffectation" future, filtré, trieé selon l'appel Ajax de dataTables 
     * 
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search  
     *
     * @return array
     */
    public function getListeAffectationFuture($start, $length, $orders, $search): array
    {
        $date = new DateTime();
        $where = 
            [
                'a.dateAffectation >= :date',
            ];
        $parameter = 
            [
                'date' => $date->sub(new DateInterval('P1D')), 
            ]; 

        $otherConditions = ['where' => $where , 'parameter' => $parameter]  ; 

        return $this->getAffectation($start, $length, $orders, $search, $otherConditions);                            
    }
    
    /**
     * Retourne le nombre total d'objets "Affectation" futures non filtrés
     * 
     * @return int
     */
    public function listeAffectationFutureCount(): int
    {
        $dateTime = new DateTime();
        $date = $dateTime->sub(new DateInterval('P1D'));
        $dql = $this->createQueryBuilder('a')
                    ->select('a')
                    ->where('a.dateAffectation >= :date')
                    ->setParameter('date', $date)
                    ->getQuery()
                    ->getResult()    
        ; 
        
        return count($dql);   
    }

    /**
     * Retourne un tableau d'objets "Affectation" passés, filtré, trieé selon l'appel Ajax de dataTables 
     * 
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search  
     * 
     * @return array
     */
    public function getListeAffectationArchive($start, $length, $orders, $search): array
    {
        $date = new DateTime();
        $where = 
            [
                'a.dateAffectation < :date'
            ];
        $parameter = 
            [
                'date' => $date->sub(new DateInterval('P1D'))
            ]; 

        $otherConditions = ['where' => $where , 'parameter' => $parameter]  ; 

        return $this->getAffectation($start, $length, $orders, $search, $otherConditions); 
    }

    /**
     * Retourne le nombre total d'objets "Affectation" passés non filtrés
     * 
     * @return int
     */
    public function listeAffectationArchiveCount(): int
    {
        $date = new DateTime();
        $dql = $this->createQueryBuilder('a')
                    ->andWhere('a.dateAffectation < :val')
                    ->setParameter('val', $date->sub(new DateInterval('P1D')))
                    ->orderBy('a.dateAffectation', 'DESC')
                    ->getQuery()
                    ->getResult()        
        ;
        return count($dql); 
    }  

    /**
     * Retourne un tableau d'objets "Affectation" futures des chauffeurs innactifs, filtré, trieé selon l'appel Ajax de dataTables 
     *
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search 
     * 
     * @return array
     */
    public function getListeAffectationChauffeurInactif($start, $length, $orders, $search): array
    {
        $date = new DateTime();
        $where = 
            [
                'a.dateAffectation >= :date',
                'c.statutChauffeur = :inactif'
            ];
        $parameter = 
            [
                'date' => $date->sub(new DateInterval('P1D')),
                'inactif' => 0
            ]; 

        $otherConditions = ['where' => $where , 'parameter' => $parameter]  ; 

        return $this->getAffectation($start, $length, $orders, $search, $otherConditions); 
           
    }

    /**
     * Retourne le nombre total d'objets "Affectation" futures des chauffeurs innactifs
     *
     * @return int
     */
    public function getAffectationChauffeurInactifCount(): int
    {
        $dateTime = new DateTime();
        $date = $dateTime->sub(new DateInterval('P1D'));
        $dql = $this->createQueryBuilder('a')
                    ->select('a')
                    ->leftJoin('a.chauffeur', 'c')
                    ->andWhere('a.dateAffectation >= :date')
                    ->setParameter('date', $date)
                    ->andWhere('c.statutChauffeur = :inactif')
                    ->setParameter('inactif', 0)            
                    ->getQuery()
                    ->getResult()    
        ; 
        
        return count($dql);     
    }

    /**
     * Retourne un tableau d'objets "Affectation" futures des véhicules innactifs, filtré, trieé selon l'appel Ajax de dataTables 
     *
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search 
     * 
     * @return array
     */
    public function getListeAffectationVehiculeInactif($start, $length, $orders, $search): array
    {
        $date = new DateTime();
        $where = 
            [
                'a.dateAffectation >= :date',
                'v.statutVehicule = :inactif'
            ];
        $parameter = 
            [
                'date' => $date->sub(new DateInterval('P1D')),
                'inactif' => 0
            ]; 

        $otherConditions = ['where' => $where , 'parameter' => $parameter]  ; 

        return $this->getAffectation($start, $length, $orders, $search, $otherConditions); 
    }

    /**
     * Retourne le nombre total d'objets "Affectation" futures des véhicules innactifs
     *
     * @return int
     */
    public function getAffectationVehiculeInactifCount(): int
    {
        $dateTime = new DateTime();
        $date = $dateTime->sub(new DateInterval('P1D'));
        $dql = $this->createQueryBuilder('a')
                    ->select('a')
                    ->leftJoin('a.vehicule', 'v')
                    ->where('a.dateAffectation >= :date')
                    ->setParameter('date', $date)
                    ->andWhere('v.statutVehicule = :inactif')
                    ->setParameter('inactif', 0)            
                    ->getQuery()
                    ->getResult()    
        ; 
        
        return count($dql); 
    }

    /**
     * Prépare les données pour la requête Ajax du plugin "dataTables", retourne un tableau d'abjets "Affectation"
     * 
     * @param int $start
     * @param int $length
     * @param array $orders
     * @param array $search
     * @param array $otherConditions | null
     *
     * @return array
     */
    public function getAffectation($start, $length, $orders, $search, $otherConditions = null): array
    {
        // Requête principale
        $query = $this->createQueryBuilder('a');
        // Requête pour le nombre d'enregistrement
        $countQuery = $this->createQueryBuilder('a');
        $countQuery->select('count(a)');            
        
        // clause join
        $query      ->leftJoin('a.chauffeur', 'c')
                    ->leftJoin('a.vehicule', 'v') 
                    ->leftJoin('v.modeleVehicule', 'm')
                    ->leftJoin('a.tournee', 't');
                   
        $countQuery ->leftJoin('a.chauffeur', 'c')
                    ->leftJoin('a.vehicule', 'v')
                    ->leftJoin('v.modeleVehicule', 'm')
                    ->leftJoin('a.tournee', 't');

        // clause where                   
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
        // Filtre sur le critère de recherche (minimum 2 caractères)
        if ($search['value'] != '' && strlen($search['value']) >= 2)
        {
            // $searchItem : terme de la recherche 
            // Supprime les espaces en début et en fin de chaîne
            $searchItem = trim($search['value']) ;
            $tabSearchQuery = null;
            $tabSearchQuery =   [ 
                                    'a.dateAffectation' => ':val',
                                    'c.nomChauffeur' => ':val',
                                    'c.prenomChauffeur' => ':val',
                                    'v.immatriculationVehicule' => ':val',
                                    'm.marqueModeleVehicule' => ':val',
                                    'm.nomModeleVehicule' => ':val',
                                    't.numTournee' => ':val',
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
                    case 'Date':
                    {
                        $orderColumn = 'a.dateAffectation';
                        break;
                    }
                    case 'Driver':
                    {
                        $orderColumn = 'c.nomChauffeur';
                        break;
                    }
                    case 'Vehicle':
                    {
                        $orderColumn = 'v.immatriculationVehicule';
                        break;
                    }
                    case 'Brand':
                    {
                        $orderColumn = 'm.marqueModeleVehicule';
                        break;
                    }
                    case 'Round':
                    {
                        $orderColumn = 't.numTournee';
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
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return array("results" => $results, "countResult" => $countResult);
    }

    /**
     * Retourne les objets "Affectations" posterieures à la date du jour (pour un chauffeur)
     * 
     * @param Chauffeur $chauffeur
     * @param DateTime $date
     * 
     * @return array
     */
    public function findByDateFuturChauffeur($chauffeur): array
    {
        return $this->createQueryBuilder('a')   
            ->andwhere('a.chauffeur = :chauffeur')
            ->setParameter('chauffeur', $chauffeur)
            ->andWhere('a.dateAffectation >= :date')           
            ->setParameter('date', new DateTime('today'))
            ->orderBy('a.dateAffectation', 'ASC')  
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Retourne les objets "Affectations" posterieures à la date du jour (pour un véhicule)
     * 
     * @param Vehicule $vehicule
     * @param DateTime $date
     * 
     * @return array
     */
    public function findByDateFuturVehicule($vehicule): array
    {
        return $this->createQueryBuilder('a')   
            ->andwhere('a.vehicule = :vehicule')
            ->setParameter('vehicule', $vehicule)
            ->andWhere('a.dateAffectation >= :date')           
            ->setParameter('date', new DateTime('today'))
            ->orderBy('a.dateAffectation', 'ASC')  
            ->getQuery()
            ->getResult()
        ;
    }
}
