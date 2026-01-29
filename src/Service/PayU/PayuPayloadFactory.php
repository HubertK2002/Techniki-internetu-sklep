<?php

namespace App\Service\PayU;

use App\Entity\Order;
use Symfony\Component\HttpFoundation\RequestStack;

final class PayuPayloadFactory
{
	public function __construct(
		private readonly PayuConfig $cfg,
		private readonly RequestStack $requestStack,
	) {}

	public function createForOrder(Order $order): array
	{
		$request = $this->requestStack->getCurrentRequest();
		$ip = $request?->getClientIp() ?: '127.0.0.1';

		$continueUrl = rtrim($this->cfg->continueBase, '/').'/order/'.$order->getId();

		$products = [];
		$total = 0;

		$cart = $order->getCart();
		foreach ($cart->getItems() as $item) {
			$p = $item->getProduct();
			$unit = (float) ($p?->getPrice() ?? 0);
			$qty = (int) $item->getQuantity();

			$products[] = [
				'name' => $p ? $p->getName() : 'Produkt',
				'unitPrice' => (string) $this->toGrosze($unit),
				'quantity' => $qty,
			];

			$total += $this->toGrosze($unit) * $qty;
		}

		$payload = [
			'customerIp' => $ip,
			'merchantPosId' => $this->cfg->posId,
			'description' => 'Zamówienie #'.$order->getId(),
			'currencyCode' => 'PLN',
			'totalAmount' => (string) $total,
			'continueUrl' => $continueUrl,
			'products' => $products,
		];

		$attempt = $order->getPayuAttempt();
		if ($attempt < 1) {
			$attempt = 1;
		}

		$payload['extOrderId'] = $order->getId().'-'.$attempt;

		// notifyUrl tylko gdy ustawione (na razie puste)
		if (!empty($this->cfg->notifyUrl)) {
			$payload['notifyUrl'] = $this->cfg->notifyUrl;
		}

		if ($order->getUser()) {
			$u = $order->getUser();
			$payload['buyer'] = [
				'email' => $u->getEmail(),
				'firstName' => $u->getFirstName() ?? '',
				'lastName' => $u->getLastName() ?? '',
				'language' => 'pl',
			];
		}

		return $payload;
	}

	private function toGrosze(float $pln): int
	{
		return (int) round($pln * 100);
	}
}
