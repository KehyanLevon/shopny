<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function createFilteredQuery(
        ?string $search,
        ?bool $isVerified,
        ?string $role,
        string $sortBy,
        string $sortDir
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('u');

        if ($search) {
            $term = '%' . mb_strtolower($search) . '%';
            $qb
                ->andWhere('LOWER(u.name) LIKE :term OR LOWER(u.surname) LIKE :term OR LOWER(u.email) LIKE :term')
                ->setParameter('term', $term);
        }

        if ($isVerified !== null) {
            if ($isVerified) {
                $qb->andWhere('u.verifiedAt IS NOT NULL');
            } else {
                $qb->andWhere('u.verifiedAt IS NULL');
            }
        }

        if ($role) {
            $qb
                ->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%"'.$role.'"%');
        }

        $allowed = ['createdAt', 'verifiedAt', 'id'];
        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'createdAt';
        }

        $qb->orderBy('u.' . $sortBy, $sortDir);

        return $qb;
    }
}
