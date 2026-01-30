<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

	public function findAllWithProductsCount(): array
	{
		return $this->createQueryBuilder('c')
			->select('c AS category')
			->orderBy('c.Name', 'ASC')
			->getQuery()
			->getResult();
	}
}
