<?php

namespace App\Repository;

use App\Entity\Amende;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Amende|null find($id, $lockMode = null, $lockVersion = null)
 * @method Amende|null findOneBy(array $criteria, array $orderBy = null)
 * @method Amende[]    findAll()
 * @method Amende[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AmendeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Amende::class);
    }

    /**
     * Retourne un tableau d'objets "Amende" d'un mois et d'une année envoyé en paramètre
     *
     * @param string $month
     * @param string $year
     *  
     * @return array
     */
    public function getAmendeMois($month, $year): array
    {
        return $this->createQueryBuilder('a') 
            ->select('a') 
            ->where ('MONTH(a.dateAmende) = :month')
            ->setParameter('month', $month)
            ->andWhere ('YEAR(a.dateAmende) = :year') 
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult()
        ;
    }
}
