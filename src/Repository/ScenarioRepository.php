<?php

namespace App\Repository;

use App\Entity\Scenario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scenario>
 *
 * @method Scenario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scenario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scenario[]    findAll()
 * @method Scenario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScenarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scenario::class);
    }

    /**
     * @return array Returns an array of 'base_scenario' JSON data for a given activityId
     */
    public function findScenarioByActivityId(int $activityId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.activity = :activityId')
            ->setParameter('activityId', $activityId)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
