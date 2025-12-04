<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function createFilteredQuery(
        ?int $categoryId,
        ?int $sectionId,
        ?bool $isActive,
        ?string $search,
        string $sortBy,
        string $sortDir
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('c.section', 's')
            ->addSelect('c', 's');

        if ($categoryId !== null) {
            $qb
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($sectionId !== null) {
            $qb
                ->andWhere('s.id = :sectionId')
                ->setParameter('sectionId', $sectionId);
        }

        if ($search !== null && $search !== '') {
            $term = '%' . mb_strtolower($search) . '%';
            $qb
                ->andWhere('LOWER(p.title) LIKE :q OR LOWER(p.description) LIKE :q')
                ->setParameter('q', $term);
        }

        if ($isActive !== null) {
            $qb
                ->andWhere('p.isActive = :isActive')
                ->setParameter('isActive', $isActive);
        }

        $allowedSorts = ['price', 'createdAt', 'title'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'createdAt';
        }

        $qb->orderBy('p.' . $sortBy, $sortDir)
            ->distinct();

        return $qb;
    }
}
