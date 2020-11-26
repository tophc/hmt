<?php

namespace App\Repository;

use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Vehicule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicule[]    findAll()
 * @method Vehicule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    /**
     * Retourne les objets "Vehicule" dont la plaque correspond au $query
     * 
     * @return Vehicule[]
     */
    public function findAllMatching(string $query, int $limit = 10)
    {
        return $this->createQueryBuilder('v')
                    ->andWhere('v.immatriculationVehicule LIKE :query')
                    ->setParameter('query', '%'.$query.'%')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult()
        ;
    }
}
