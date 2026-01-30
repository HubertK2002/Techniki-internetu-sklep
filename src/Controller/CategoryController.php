<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;

final class CategoryController extends AbstractController
{
   #[Route('/categories', name: 'category_index')]
	public function index(CategoryRepository $repo): Response
	{
		return $this->render('category/categories.html.twig', [
			'categories' => $repo->findAll(),
		]);
	}
}
