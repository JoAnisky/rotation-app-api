<?php

namespace App\Repository;

use App\Entity\StandParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StandParticipation>
 *
 * @method StandParticipation|null find($id, $lockMode = null, $lockVersion = null)
 * @method StandParticipation|null findOneBy(array $criteria, array $orderBy = null)
 * @method StandParticipation[]    findAll()
 * @method StandParticipation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StandParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StandParticipation::class);
    }

//    /**
//     * @return StandParticipation[] Returns an array of StandParticipation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StandParticipation
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
