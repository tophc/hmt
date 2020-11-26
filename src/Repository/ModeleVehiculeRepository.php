<?php

namespace App\Repository;

use App\Entity\ModeleVehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ModeleVehicule|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModeleVehicule|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModeleVehicule[]    findAll()
 * @method ModeleVehicule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModeleVehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModeleVehicule::class);
    }
}
