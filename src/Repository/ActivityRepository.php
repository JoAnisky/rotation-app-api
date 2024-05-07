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
     * Checks if a pincode still exists in the database
     * 
     * @param string $code Code to check
     * @param string $type Code type (participantCode or animatorCode)
     * @return bool
     */
    public function codeExists(string $code, string $type): bool
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('count(a.id)')
            ->where("a.$type = :code")
            ->setParameter('code', $code);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count > 0;
    }
}
