<?php

namespace App\Service\PayU;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

final class PayuPaymentService
{
	public function __construct(
		private readonly EntityManagerInterface $em,
		private readonly PayuClient $client,
	) {}

	public function startOrContinue(Order $order, array $payuPayload): string
	{
		$this->assertCanPayu($order);
		$this->assertNotPaid($order);

		if ($order->getPayuOrderId()) {
			return $this->continueExisting($order);
		}

		return $this->createNew($order, $payuPayload);
	}

	private function assertNotPaid(Order $order): void
	{
		if ($order->getStatus() === 'paid' || $order->getPaidAt()) {
			throw new \RuntimeException('Zamówienie jest już opłacone.');
		}
	}

	private function continueExisting(Order $order): string
	{
		if ($order->getPayuStatus() === 'COMPLETED' || $order->getPaidAt() || $order->getStatus() === 'paid') {
			throw new \RuntimeException('Płatność już zakończona.');
		}

		if ($order->getPayuRedirectUri()) {
			return $order->getPayuRedirectUri();
		}

		$data = $this->client->getOrder($order->getPayuOrderId());
		$this->applyPayuStatus($order, $data);
		$this->em->flush();

		if ($order->getPayuStatus() === 'COMPLETED') {
			throw new \RuntimeException('Płatność już zakończona.');
		}

		if ($order->getPayuRedirectUri()) {
			return $order->getPayuRedirectUri();
		}

		throw new \RuntimeException('Nie można kontynuować płatności (brak linku). Użyj "Zaktualizuj status" albo "Nowa płatność".');
	}

	private function createNew(Order $order, array $payuPayload): string
	{
		if ($order->getPayuAttempt() < 1) {
			$order->setPayuAttempt(1);
			$this->em->flush();
		}
		$result = $this->client->createOrder($payuPayload);

		if (($result['error'] ?? null) === 'ORDER_NOT_UNIQUE') {
			$orderId = $result['orderId'] ?? null;

			if ($orderId) {
				$order->setPayuOrderId($orderId);
				$this->em->flush();
				$this->refreshStatus($order);
			}

			throw new \RuntimeException('Płatność PayU już została rozpoczęta. Użyj "Zaktualizuj status płatności".');
		}

		$redirect = $result['redirectUri'] ?? null;
		$orderId  = $result['orderId'] ?? null;

		if (!$orderId || !$redirect) {
			throw new \RuntimeException('Niepełna odpowiedź PayU (orderId/redirectUri).');
		}

		$order->setPayuOrderId($orderId);
		$order->setPayuRedirectUri($redirect);
		$order->setPayuStatus($result['status']['statusCode'] ?? null);
		$order->touch();

		$this->em->flush();

		return $redirect;
	}

	public function restartPayment(Order $order, array $payuPayload): string
	{
		$this->assertCanPayu($order);

		// jeśli już opłacone – nie restartujemy
		if ($order->getStatus() === 'paid' || $order->getPaidAt()) {
			throw new \RuntimeException('Zamówienie jest już opłacone.');
		}

		// zwiększamy attempt i czyścimy stare dane PayU
		$order->increasePayuAttempt();
		$order->setPayuOrderId(null);
		$order->setPayuRedirectUri(null);
		$order->setPayuStatus(null);
		$order->setPayuLastStatusCheckAt(null);

		$this->em->flush();

		// payload musi mieć nowy extOrderId (factory już to uwzględnia)
		return $this->createNew($order, $payuPayload);
	}

	public function refreshStatus(Order $order): void
	{
		$this->assertCanRefresh($order);

		$data = $this->client->getOrder($order->getPayuOrderId());
		$this->applyPayuStatus($order, $data);

		$order->setPayuLastStatusCheckAt(new \DateTimeImmutable());
		$order->touch();

		$this->em->flush();
	}

	private function applyPayuStatus(Order $order, array $data): void
	{
		// 1) jeśli API zwraca listę orders
		$status = $data['orders'][0]['status'] ?? null;

		// 2) jeśli zwraca pojedynczy order z obiektem status
		if (!$status) {
			$status = $data['status']['statusCode'] ?? null;
		}

		$order->setPayuStatus($status);

		if ($status === 'COMPLETED') {
			$order->setStatus('paid');
			if (!$order->getPaidAt()) {
				$order->setPaidAt(new \DateTimeImmutable());
			}
		}
	}


	private function assertCanPayu(Order $order): void
	{
		if ($order->getPaymentMethod() !== 'payu') {
			throw new \RuntimeException('Ta płatność nie jest PayU.');
		}
		if ($order->getStatus() !== 'confirmed') {
			throw new \RuntimeException('Zamówienie musi być złożone (confirmed), aby płacić.');
		}
	}

	private function assertCanRefresh(Order $order): void
	{
		if (!$order->getPayuOrderId()) {
			throw new \RuntimeException('Brak PayU orderId.');
		}
		if ($order->getPaymentMethod() !== 'payu') {
			throw new \RuntimeException('To nie jest PayU.');
		}
	}
}
