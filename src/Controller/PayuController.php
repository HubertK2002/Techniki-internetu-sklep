<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\PayU\PayuPaymentService;
use App\Service\PayU\PayuPayloadFactory;
use App\Service\PayU\PayuWebhookVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PayuController extends AbstractController
{
	#[Route('/payu/notify', name: 'payu_notify', methods: ['POST'])]
	public function notify(
		Request $request,
		PayuWebhookVerifier $verifier,
		PayuPaymentService $payu,
		EntityManagerInterface $em,
	): Response {
		$rawBody = $request->getContent();
		if (!$verifier->verify($request, $rawBody)) {
			return new Response('Invalid PayU signature.', Response::HTTP_UNAUTHORIZED);
		}

		$notification = json_decode($rawBody, true);
		if (!is_array($notification)) {
			return new Response('Invalid JSON.', Response::HTTP_BAD_REQUEST);
		}

		$order = $this->findOrderForNotification($notification, $em);
		if (!$order) {
			return new Response('Order not found.', Response::HTTP_NOT_FOUND);
		}

		try {
			$payu->applyNotification($order, $notification);
		} catch (\Throwable $e) {
			return new Response('Notification rejected.', Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		return new Response('', Response::HTTP_OK);
	}

	#[Route('/order/{id}/payu/start', name: 'payu_start', methods: ['POST'])]
	public function start(Order $order, PayuPaymentService $payu, PayuPayloadFactory $factory): Response
	{
		// access: tylko właściciel i tylko gdy editable/płatne itd.
		$this->denyAccessUnlessGranted('ROLE_USER');
		if ($order->getUser() !== $this->getUser()) {
			throw $this->createAccessDeniedException();
		}

		try {
			$payload = $factory->createForOrder($order);
			$redirect = $payu->startOrContinue($order, $payload);
			return $this->redirect($redirect);
		} catch (\Throwable $e) {
			$this->addFlash('error', $e->getMessage());
			return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
		}
	}

	#[Route('/order/{id}/payu/refresh', name: 'payu_refresh', methods: ['POST'])]
	public function refresh(Order $order, PayuPaymentService $payu): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		if ($order->getUser() !== $this->getUser()) {
			throw $this->createAccessDeniedException();
		}

		$payu->refreshStatus($order);

		return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
	}

	#[Route('/order/{id}/payu/restart', name: 'payu_restart', methods: ['POST'])]
	public function restart(Order $order, PayuPaymentService $payu, PayuPayloadFactory $factory): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		if ($order->getUser() !== $this->getUser()) {
			throw $this->createAccessDeniedException();
		}

		try {
			$payload = $factory->createForOrder($order);
			$redirect = $payu->restartPayment($order, $payload);
			return $this->redirect($redirect);
		} catch (\Throwable $e) {
			$this->addFlash('error', $e->getMessage());
			return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
		}
	}

	private function findOrderForNotification(array $notification, EntityManagerInterface $em): ?Order
	{
		$repo = $em->getRepository(Order::class);
		$payuOrderId = $notification['order']['orderId'] ?? null;

		if (is_string($payuOrderId) && $payuOrderId !== '') {
			$order = $repo->findOneBy(['PayuOrderId' => $payuOrderId]);
			if ($order instanceof Order) {
				return $order;
			}
		}

		$extOrderId = $notification['order']['extOrderId'] ?? null;
		if (!is_string($extOrderId)) {
			return null;
		}

		if (
			!preg_match('/^shop-(\d+)-(\d+)-\d+$/', $extOrderId, $matches)
			&& !preg_match('/^(\d+)-(\d+)$/', $extOrderId, $matches)
		) {
			return null;
		}

		$order = $repo->find((int) $matches[1]);
		if (!$order instanceof Order || $order->getPayuAttempt() !== (int) $matches[2]) {
			return null;
		}

		return $order;
	}
}
