<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
   #[Route('/categories', name: 'category_index')]
	public function index(CategoryRepository $repo): Response
	{
		$rows = $repo->findAllWithProductsCount();

		return $this->render('category/index.html.twig', [
			'rows' => $rows,
		]);
	}
}
