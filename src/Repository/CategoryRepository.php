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

	/**
	 * @return Category[]
	 */
	public function findRoot(): array
	{
		return $this->createQueryBuilder('c')
			->where('c.parent IS NULL')
			->orderBy('c.Name', 'ASC')
			->getQuery()
			->getResult();
	}

	/**
	 * @return int[] IDs wszystkich potomków (rekurencyjnie)
	 */
	public function findDescendantIds(Category $root): array
	{
		$rows = $this->createQueryBuilder('c')
			->select('c.id AS id, IDENTITY(c.parent) AS parentId')
			->getQuery()
			->getArrayResult();

		$childrenByParent = [];
		foreach ($rows as $r) {
			$pid = $r['parentId'] !== null ? (int) $r['parentId'] : null;
			$id  = (int) $r['id'];

			$childrenByParent[$pid][] = $id;
		}

		$rootId = (int) $root->getId();
		$result = [];
		$queue = $childrenByParent[$rootId] ?? [];

		while ($queue) {
			$id = array_shift($queue);
			if (isset($result[$id])) {
				continue;
			}
			$result[$id] = true;

			foreach ($childrenByParent[$id] ?? [] as $childId) {
				$queue[] = $childId;
			}
		}

		return array_keys($result);
	}

	public function getTreeIndex(): array
	{
		$rows = $this->createQueryBuilder('c')
			->select('c.id AS id, c.Name AS name, IDENTITY(c.parent) AS parentId')
			->orderBy('c.Name', 'ASC')
			->getQuery()
			->getArrayResult();

		$items = [];
		$childrenByParent = [];
		$roots = [];

		foreach ($rows as $r) {
			$id = (int) $r['id'];
			$pid = $r['parentId'] !== null ? (int) $r['parentId'] : null;

			$items[$id] = [
				'id' => $id,
				'name' => $r['name'],
				'slug' => Category::slugify((string) $r['name']),
				'parentId' => $pid,
			];

			$childrenByParent[$pid][] = $id;

			if ($pid === null) {
				$roots[] = $id;
			}
		}

		return [$items, $childrenByParent, $roots];
	}
}
