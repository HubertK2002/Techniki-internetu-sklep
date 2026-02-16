<?php

namespace App\Twig;

use App\Service\OrderValueLabelService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class OrderValueLabelExtension extends AbstractExtension
{
	public function __construct(private readonly OrderValueLabelService $labels) {}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('order_status_label', [$this, 'orderStatusLabel']),
			new TwigFunction('order_payment_method_label', [$this, 'orderPaymentMethodLabel']),
			new TwigFunction('order_delivery_method_label', [$this, 'orderDeliveryMethodLabel']),
			new TwigFunction('order_payu_status_label', [$this, 'orderPayuStatusLabel']),
		];
	}

	public function orderStatusLabel(?string $value): string
	{
		return $this->labels->status($value);
	}

	public function orderPaymentMethodLabel(?string $value): string
	{
		return $this->labels->paymentMethod($value);
	}

	public function orderDeliveryMethodLabel(?string $value): string
	{
		return $this->labels->deliveryMethod($value);
	}

	public function orderPayuStatusLabel(?string $value): string
	{
		return $this->labels->payuStatus($value);
	}
}

