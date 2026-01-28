<?php

namespace App\Twig;

use App\Service\CartSummary;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class CartCountExtension extends AbstractExtension implements GlobalsInterface
{
	public function __construct(private readonly CartSummary $cartSummary) {}

	public function getGlobals(): array
	{
		return [
			'cart_count' => $this->cartSummary->getItemsCount(),
		];
	}
}
