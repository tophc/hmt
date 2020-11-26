<?php
 
namespace App\Repository;

use DateTime;
use DateInterval;
use App\Entity\PermisConduire;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method PermisConduire|null find($id, $lockMode = null, $lockVersion = null)
 * @method PermisConduire|null findOneBy(array $criteria, array $orderBy = null)
 * @method PermisConduire[]    findAll()
 * @method PermisConduire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermisConduireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermisConduire::class);
    }

    /**
     * Retourne un tableau d'objets "PermisConduire" arrivant à échéance
     *
     * @return Array
     */
    public function  getPermisConduireBientot(): Array
    {
        $today = new DateTime('today');
        $date = clone $today;
        $date->add(new DateInterval('P1M'));
        return $this->createQueryBuilder('p') 
                    ->select('p')    
                    ->where('p.dateValPermisConduire <= :date')
                    ->setParameter('date', $date)
                    ->andWhere('p.dateValPermisConduire > :today')
                    ->setParameter('today', $today)
                    ->getQuery()
                    ->getResult()
        ; 
    }

    /**
     * Retourne un tableau d'objets "PermisConduire" expirés
     *
     * @return Array
     */
    public function  getPermisConduireExpire(): Array
    {
        $today = new DateTime('today');
        return $this->createQueryBuilder('p') 
                    ->select('p')    
                    ->where('p.dateValPermisConduire <= :today')
                    ->setParameter('today', $today)
                    ->getQuery()
                    ->getResult()
        ; 
    }
}
