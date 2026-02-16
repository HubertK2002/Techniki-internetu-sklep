<?php

namespace App\Repository;

use App\Entity\Opinion;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Opinion>
 */
class OpinionRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Opinion::class);
	}

	/**
	 * @return array{average: float, count: int}
	 */
	public function getAverageAndCountForProduct(Product $product): array
	{
		$row = $this->createQueryBuilder('o')
			->select('AVG(o.Rating) AS avgRating, COUNT(o.id) AS cnt')
			->andWhere('o.Product = :product')
			->setParameter('product', $product)
			->getQuery()
			->getSingleResult();

		return [
			'average' => isset($row['avgRating']) ? (float) $row['avgRating'] : 0.0,
			'count' => isset($row['cnt']) ? (int) $row['cnt'] : 0,
		];
	}

	/**
	 * @return array<int, int>
	 */
	public function getRatingBreakdownForProduct(Product $product): array
	{
		$rows = $this->createQueryBuilder('o')
			->select('o.Rating AS rating, COUNT(o.id) AS cnt')
			->andWhere('o.Product = :product')
			->setParameter('product', $product)
			->groupBy('o.Rating')
			->getQuery()
			->getResult();

		$breakdown = [];
		foreach ($rows as $row) {
			$breakdown[(int) $row['rating']] = (int) $row['cnt'];
		}

		return $breakdown;
	}

	/**
	 * @return Opinion[]
	 */
	public function findLatestForProduct(Product $product, int $limit = 20): array
	{
		return $this->createQueryBuilder('o')
			->leftJoin('o.User', 'u')
			->addSelect('u')
			->andWhere('o.Product = :product')
			->setParameter('product', $product)
			->orderBy('o.CreatedAt', 'DESC')
			->setMaxResults($limit)
			->getQuery()
			->getResult();
	}
}
