<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Section::class);
    }

    public function createFilteredQuery(
        ?bool $isActive,
        ?string $search
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('s');

        if ($search !== null && $search !== '') {
            $term = '%' . mb_strtolower($search) . '%';
            $qb
                ->andWhere('LOWER(s.title) LIKE :q OR LOWER(s.slug) LIKE :q')
                ->setParameter('q', $term);
        }

        if ($isActive !== null) {
            $qb
                ->andWhere('s.isActive = :isActive')
                ->setParameter('isActive', $isActive);
        }

        $qb->orderBy('s.id', 'ASC');

        return $qb;
    }
}
