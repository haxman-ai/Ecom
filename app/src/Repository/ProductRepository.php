<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Recherche LIKE insensible à la casse sur title, description et nom de catégorie.
     * Sans mot-clé, retourne tous les produits.
     *
     * @return Product[]
     */
    public function findByKeyword(?string $keyword): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.category', 'c')
            ->orderBy('p.title', 'ASC');

        if ($keyword !== null && $keyword !== '') {
            $pattern = '%' . $keyword . '%';
            $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(p.title)', 'LOWER(:kw)'),
                        $qb->expr()->like('LOWER(p.description)', 'LOWER(:kw)'),
                        $qb->expr()->like('LOWER(c.name)', 'LOWER(:kw)')
                    )
                )
                ->setParameter('kw', $pattern);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Product[]
     */
    public function findAllWithCategory(?int $categoryId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.category', 'c')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('p.title', 'ASC');

        if ($categoryId !== null) {
            $qb->andWhere('c.id = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }
}
