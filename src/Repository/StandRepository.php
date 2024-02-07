<?php

namespace App\Repository;

use App\Entity\Stand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stand>
 *
 * @method Stand|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stand|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stand[]    findAll()
 * @method Stand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stand::class);
    }

//    /**
//     * @return Stand[] Returns an array of Stand objects
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

//    public function findOneBySomeField($value): ?Stand
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
