<?php

namespace App\Repository;

use App\Entity\Logistique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Logistique|null find($id, $lockMode = null, $lockVersion = null)
 * @method Logistique|null findOneBy(array $criteria, array $orderBy = null)
 * @method Logistique[]    findAll()
 * @method Logistique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogistiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Logistique::class);
    }
}
