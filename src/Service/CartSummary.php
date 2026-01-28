<?php

namespace App\Service;

use App\Entity\CartItem;

final class CartSummary
{
	public function __construct(private readonly CartService $cartService) {}

	public function getItemsCount(): int
	{
		$cart = $this->cartService->getCurrentCart(false);
		if (!$cart) {
			return 0;
		}

		$count = 0;
		foreach ($cart->getItems() as $item) {
			if ($item instanceof CartItem) {
				$count += $item->getQuantity(); // suma sztuk
			}
		}

		return $count;
	}
}
