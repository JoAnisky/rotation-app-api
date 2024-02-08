<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Creates the base query builder for retrieving users by role.
     * This method constructs the basic query builder with the WHERE clause to filter users by their roles.
     *
     * @param string $roles - The role to search for
     * @return \Doctrine\ORM\QueryBuilder The query builder object
     */
    private function createBaseQuery(string $roles)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :roles')
            ->setParameter('roles', '%' . $roles . '%');
    }

    /**
     * Retrieves all Users based on their role
     * @param string $roles - The role to search for
     * @return array
     */
    public function getUsersByRole(string $roles): array
    {
        return $this->createBaseQuery($roles)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieves a User based on his role and id
     * @param string $roles - The role to search for
     * @param int $id - Id of the person to search for
     * @return array
     */
    public function getUserByRoleAndId(string $roles, int $id): array
    {
        return $this->createBaseQuery($roles)
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

}
