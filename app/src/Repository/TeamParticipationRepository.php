<?php

namespace App\Repository;

use App\Entity\TeamParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamParticipation>
 *
 * @method TeamParticipation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamParticipation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamParticipation[]    findAll()
 * @method TeamParticipation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamParticipation::class);
    }

//    /**
//     * @return TeamParticipation[] Returns an array of TeamParticipation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TeamParticipation
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
