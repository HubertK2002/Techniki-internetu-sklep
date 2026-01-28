<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Repository\CategoryRepository;

final class ProductController extends AbstractController
{
	#[Route('/products', name: 'product_index')]
	public function products_list(ProductRepository $repo, Request $request, CategoryRepository $categoryRepository): Response
	{
		$page  = max(1, (int) $request->query->get('page', 1));
		$limit = max(1, min(100, (int) $request->query->get('limit', 24)));
		$asortId = $request->query->getInt('asort', 0);
		$q = trim((string) $request->query->get('q', ''));

		$qb = $repo->createQueryBuilder('p')
			->orderBy('p.id', 'DESC')
			->setFirstResult(($page - 1) * $limit);
		if ($asortId > 0) {
			$qb->andWhere('p.Category = :cat')
				->setParameter('cat', $asortId);
		}
		if ($q !== '') {
			$qb->andWhere('p.Name LIKE :q OR p.Description LIKE :q')
				->setParameter('q', '%'.$q.'%');
		}
		$qb->setMaxResults($limit);

		$paginator  = new Paginator($qb, true);
		$totalItems = count($paginator);
		$totalPages = max(1, (int) ceil($totalItems / $limit));

		if ($page > $totalPages) {
			$page = $totalPages;

			$qb->setFirstResult(($page - 1) * $limit);
			$paginator = new Paginator($qb, true);
		}

		$categories = $categoryRepository->findBy([], ['Name' => 'ASC']);

		return $this->render('product/index.html.twig', [
			'products' => iterator_to_array($paginator),
			'page' => $page,
			'limit' => $limit,
			'total_pages' => $totalPages,
			'categories' => $categories,
			'asort' => $asortId,
			'q' => $q,
		]);
	}
}