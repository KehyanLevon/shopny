<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function createFilteredQuery(
        ?int $sectionId,
        ?bool $isActive,
        ?string $search
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.section', 's')
            ->addSelect('s');

        if ($sectionId !== null) {
            $qb
                ->andWhere('s.id = :sectionId')
                ->setParameter('sectionId', $sectionId);
        }

        if ($search !== null && $search !== '') {
            $term = '%' . mb_strtolower($search) . '%';
            $qb
                ->andWhere('LOWER(c.title) LIKE :q OR LOWER(c.slug) LIKE :q')
                ->setParameter('q', $term);
        }

        if ($isActive !== null) {
            $qb
                ->andWhere('c.isActive = :isActive')
                ->setParameter('isActive', $isActive);
        }

        $qb->orderBy('c.id', 'ASC');

        return $qb;
    }
}
