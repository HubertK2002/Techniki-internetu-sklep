<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\CartService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

final class OrderController extends AbstractController
{
	private function assertOwner(Order $order): void
	{
		$this->denyAccessUnlessGranted('ROLE_USER');

		if (!$order->getUser() || $order->getUser() !== $this->getUser()) {
			throw $this->createAccessDeniedException();
		}
	}

	#[Route('/order/{id}', name: 'order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
	public function show(int $id, EntityManagerInterface $em): Response
	{
		/** @var Order|null $order */
		$order = $em->getRepository(Order::class)->find($id);

		if (!$order) {
			throw $this->createNotFoundException();
		}

		$this->assertOwner($order);

		return $this->render('order/show.html.twig', [
			'order' => $order,
		]);
	}

	#[IsGranted('ROLE_USER')]
    #[Route('/order/create', name: 'order_create_from_cart', methods: ['POST'])]
	public function createFromCart(CartService $cartService): Response
	{
		$order = $cartService->checkout();

		$response = $this->redirectToRoute('order_show', ['id' => $order->getId()]);
		$cartService->applyCookie($response);
		return $response;
	}

	#[IsGranted('ROLE_USER')]
	#[Route('/orders', name: 'order_index', methods: ['GET'])]
	public function index(EntityManagerInterface $em): Response
	{
		$orders = $em->getRepository(\App\Entity\Order::class)->findBy(
			['User' => $this->getUser()],
			['id' => 'DESC']
		);

		return $this->render('order/index.html.twig', [
			'orders' => $orders,
		]);
	}

	#[Route('/order/{id}/delivery', name: 'order_set_delivery', requirements: ['id' => '\d+'], methods: ['POST'])]
	public function setDelivery(int $id, Request $request, EntityManagerInterface $em): Response
	{
		/** @var Order|null $order */
		$order = $em->getRepository(Order::class)->find($id);
		if (!$order) throw $this->createNotFoundException();
		$this->assertOwner($order);
		$this->assertEditable($order);

		$method = (string) $request->request->get('delivery_method', '');
		if (!in_array($method, ['courier', 'pickup'], true)) {
			$this->addFlash('error', 'Niepoprawna metoda dostawy.');
			return $this->redirectToRoute('order_show', ['id' => $id]);
		}

		$order->setDeliveryMethod($method);

		// jeśli pickup -> czyścimy adres
		if ($method === 'pickup') {
			$order->setAddressLine(null)
				->setPostalCode(null)
				->setCity(null);
		}

		$em->flush();
		return $this->redirectToRoute('order_show', ['id' => $id]);
	}

	#[Route('/order/{id}/shipping', name: 'order_set_shipping', requirements: ['id' => '\d+'], methods: ['POST'])]
	public function setShipping(int $id, Request $request, EntityManagerInterface $em): Response
	{
		/** @var Order|null $order */
		$order = $em->getRepository(Order::class)->find($id);
		if (!$order) throw $this->createNotFoundException();
		$this->assertOwner($order);
		$this->assertEditable($order);

		$delivery = $order->getDeliveryMethod();

		if ($delivery !== 'courier') {
			$this->addFlash('error', 'Dla odbioru na miejscu nie podaje się adresu.');
			return $this->redirectToRoute('order_show', ['id' => $id]);
		}

		$order->setAddressLine(trim((string)$request->request->get('address_line', '')) ?: null);
		$order->setPostalCode(trim((string)$request->request->get('postal_code', '')) ?: null);
		$order->setCity(trim((string)$request->request->get('city', '')) ?: null);

		$em->flush();
		return $this->redirectToRoute('order_show', ['id' => $id]);
	}

	#[Route('/order/{id}/payment', name: 'order_set_payment', requirements: ['id' => '\d+'], methods: ['POST'])]
	public function setPayment(int $id, Request $request, EntityManagerInterface $em): Response
	{
		/** @var Order|null $order */
		$order = $em->getRepository(Order::class)->find($id);
		if (!$order) throw $this->createNotFoundException();
		$this->assertOwner($order);
		$this->assertEditable($order);

		$method = (string) $request->request->get('payment_method', '');
		if (!in_array($method, ['payu', 'cod'], true)) {
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse(['ok' => false, 'error' => 'invalid_method'], 400);
			}
			$this->addFlash('error', 'Niepoprawna metoda płatności.');
			return $this->redirectToRoute('order_show', ['id' => $id]);
		}

		$order->setPaymentMethod($method);
		$em->flush();

		if ($request->isXmlHttpRequest()) {
			return new JsonResponse(['ok' => true, 'payment_method' => $method]);
		}

		return $this->redirectToRoute('order_show', ['id' => $id]);
	}

	#[Route('/order/{id}/confirm', name: 'order_confirm', requirements: ['id' => '\d+'], methods: ['POST'])]
	public function confirm(int $id, EntityManagerInterface $em): Response
	{
		/** @var Order|null $order */
		$order = $em->getRepository(Order::class)->find($id);
		if (!$order) throw $this->createNotFoundException();
		$this->assertOwner($order);

		if (!$order->getDeliveryMethod()) {
			$this->addFlash('error', 'Wybierz metodę dostawy.');
			return $this->redirectToRoute('order_show', ['id' => $id]);
		}

		if ($order->getDeliveryMethod() === 'courier') {
			if (!$order->getAddressLine() || !$order->getPostalCode() || !$order->getCity()) {
				$this->addFlash('error', 'Uzupełnij adres dostawy.');
				return $this->redirectToRoute('order_show', ['id' => $id]);
			}
		}

		if (!$order->getPaymentMethod()) {
			$this->addFlash('error', 'Wybierz metodę płatności.');
			return $this->redirectToRoute('order_show', ['id' => $id]);
		}

		$order->setStatus('confirmed');
		$em->flush();

		$this->addFlash('success', 'Zamówienie złożone.');
		return $this->redirectToRoute('order_show', ['id' => $id]);
	}

	private function assertEditable(\App\Entity\Order $order): void
	{
		if ($order->getStatus() !== 'new') {
			throw $this->createAccessDeniedException('Zamówienie jest już złożone.');
		}
	}

}
