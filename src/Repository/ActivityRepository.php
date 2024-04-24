<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 *
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * @return array Returns an array of competitive stand details from JSON field
     */
    public function findCompetitiveStands(int $activityId): array
    {
        $activity = $this->find($activityId);
        if (!$activity) {
            return [];
        }

        // Get stands data, which might be null or an array
        $stands = $activity->getStands();

        // Check if stands is null or an empty array
        if (empty($stands)) {
            return [];
        }

        // Filter stands to return only those that are competitive
        $competitiveStands = array_filter($stands, function ($stand) {
            return isset($stand['nbTeamsOnStand']) && $stand['nbTeamsOnStand'] > 1;
        });

        return $competitiveStands;
    }

    //    public function findOneBySomeField($value): ?Activity
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
