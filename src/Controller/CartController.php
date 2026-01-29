<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CartController extends AbstractController
{
	#[Route('/cart', name: 'cart_show', methods: ['GET'])]
	public function show(CartService $cartService): Response
	{
		$cart = $cartService->getCurrentCart(false);

		$response = $this->render('cart/show.html.twig', [
			'cart' => $cart,
		]);

		$cartService->applyCookie($response);

		return $response;
	}

	#[Route('/cart/add/{id}', name: 'cart_add', methods: ['POST'])]
	public function add(int $id, Request $request, \App\Service\CartService $cartService): Response
	{
		$qty = max(1, (int) $request->request->get('qty', 1));

		$cart = $cartService->addProduct($id, $qty);

		$count = 0;
		foreach ($cart->getItems() as $item) {
			$count += $item->getQuantity();
		}

		if ($request->isXmlHttpRequest() || str_contains((string) $request->headers->get('accept'), 'application/json')) {
			$response = new JsonResponse(['ok' => true, 'count' => $count]);
			$cartService->applyCookie($response);
			return $response;
		}

		$response = $this->redirectToRoute('cart_show');
		$cartService->applyCookie($response);
		return $response;
	}

	#[Route('/cart/set/{id}', name: 'cart_set_qty', methods: ['POST'])]
	public function setQty(int $id, Request $request, CartService $cartService): Response
	{
		$qty = max(1, (int) $request->request->get('qty', 1));
		$cartService->setQuantity($id, $qty);

		$response = $this->redirectToRoute('cart_show');
		$cartService->applyCookie($response);

		return $response;
	}

	#[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
	public function remove(int $id, CartService $cartService): Response
	{
		$cartService->removeProduct($id);

		$response = $this->redirectToRoute('cart_show');
		$cartService->applyCookie($response);

		return $response;
	}
}
