<?php

namespace App\Repository;

use App\Entity\Gamemaster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gamemaster>
 *
 * @method Gamemaster|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gamemaster|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gamemaster[]    findAll()
 * @method Gamemaster[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GamemasterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gamemaster::class);
    }

//    /**
//     * @return Gamemaster[] Returns an array of Gamemaster objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Gamemaster
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
