<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;

final class CategoryController extends AbstractController
{
   #[Route('/categories', name: 'category_index')]
	public function index(CategoryRepository $repo): Response
	{
		return $this->render('category/categories.html.twig', [
			'categories' => $repo->findRoot(),
		]);
	}

	#[Route('/subcategories', name: 'subcategories', methods: ['GET'])]
	public function subcategories(CategoryRepository $repo, Request $request): Response
	{
		[$items, $childrenByParent, $roots] = $repo->getTreeIndex();

		return $this->render('category/categories_on_product_page.html.twig', [
			'items' => $items,
			'childrenByParent' => $childrenByParent,
			'roots' => $roots,
			'q' => $request->query->get('q'),
		]);
	}
}
