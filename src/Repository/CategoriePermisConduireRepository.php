<?php

namespace App\Repository;

use App\Entity\CategoriePermisConduire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoriePermisConduire|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoriePermisConduire|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoriePermisConduire[]    findAll()
 * @method CategoriePermisConduire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoriePermisConduireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoriePermisConduire::class);
    }
}
