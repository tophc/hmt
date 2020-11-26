<?php

namespace App\Repository;

use App\Entity\Chauffeur;
use App\Entity\Affectation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Chauffeur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chauffeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chauffeur[]    findAll()
 * @method Chauffeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChauffeurRepository extends ServiceEntityRepository
{
    /**
     * Constructeur
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chauffeur::class);
    }

    /**
     * Retourne la liste des objet "Chauffeur" en fonction de la catégorie de permis (Valide)
     *
     * @param array $categoriePermis
     * 
     * @return array
     */
    public function getChauffeurByCategoriePermis($categoriePermis)
    {
        return $this->createQueryBuilder('c') 
                    ->select('c') 
                    ->andWhere('c.statutChauffeur = 1')
                    ->leftJoin('c.permisConduire', 'p')
                    ->leftJoin('p.categoriePermisConduires', 'x')
                    ->andWhere( 'x.nomCategoriePermisConduire IN (:cat)')
                    ->setParameter('cat', $categoriePermis)
                    ->andWhere('p.dateValPermisConduire >= :date')
                    ->setParameter('date', date('Y-m-d'))
                    ->orderBy('c.nomChauffeur', 'ASC')
                    ->getQuery()
                    ->getResult()
        ;  
    }

    /*
    SELECT chauffeur.id FROM chauffeur 
    WHERE NOT EXISTS 
        (SELECT * FROM affectation 
        WHERE affectation.chauffeur_id = chauffeur.id and affectation.date_affectation = '2020-11-18') 
    ORDER BY chauffeur.id ASC

    
    public function getChauffeurLibre()
    {

        $subquery = $this->_em->createQueryBuilder('a')
                         ->select('a')
                         ->from('App\Entity\Affectation' , 'a')                     
                         ->andwhere ('a.dateAffectation = :date')  
                         ->setParameter('date', date('Y-m-d'))
                         ->getDQL()
        ;

        $query = $this->_em->createQueryBuilder('c ') ;
                    
        $query->select('c')  
              ->from('App\Entity\Chauffeur' , 'c') 
              ->where($query->expr()->notIn('c' , $subquery))            
              ->orderBy('c.id', 'ASC')
              ->getQuery()
              ->getResult()
        ;  
        
        return $query;
    }
    */

}
