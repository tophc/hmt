<?php

namespace App\Repository;

use App\Entity\ControleTechnique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ControleTechnique|null find($id, $lockMode = null, $lockVersion = null)
 * @method ControleTechnique|null findOneBy(array $criteria, array $orderBy = null)
 * @method ControleTechnique[]    findAll()
 * @method ControleTechnique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ControleTechniqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ControleTechnique::class);
    }

    /**
     * Renvoi un tableau d'objet "ControleTechnique" avec le statutControleTechnique à 1 (ouvert = refusé) groupé par "Vehicule"
     *
     * @return array
     */
    public function getControleTechniqueRefuse(): array
    {
        return $this->createQueryBuilder('c') 
            ->select('c') 
            ->leftJoin('c.vehicule',  'v')
            ->where ('c.statutControleTechnique = :statut')
            ->setParameter('statut', '1')
            ->groupBy('v')
            ->getQuery()
            ->getResult()
        ;  
    }
}
