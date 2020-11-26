<?php

namespace App\Repository;

use App\Entity\Secretariat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Secretariat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Secretariat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Secretariat[]    findAll()
 * @method Secretariat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecretariatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Secretariat::class);
    }
}
