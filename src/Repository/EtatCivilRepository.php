<?php

namespace App\Repository;

use App\Entity\EtatCivil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EtatCivil|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtatCivil|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtatCivil[]    findAll()
 * @method EtatCivil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtatCivilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtatCivil::class);
    }
}
