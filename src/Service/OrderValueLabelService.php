<?php

namespace App\Service;

final class OrderValueLabelService
{
	public function status(?string $value): string
	{
		return match ($value) {
			'new' => 'Nowe',
			'processing' => 'W realizacji',
			'confirmed' => 'Potwierdzone',
			'paid' => 'Opłacone',
			'shipped' => 'Wysłane',
			'completed' => 'Zrealizowane',
			'canceled' => 'Anulowane',
			default => (string) $value,
		};
	}

	public function paymentMethod(?string $value): string
	{
		return match ($value) {
			'payu' => 'PayU',
			'card' => 'Karta',
			'blik' => 'BLIK',
			'transfer' => 'Przelew',
			'cod' => 'Za pobraniem',
			default => (string) $value,
		};
	}

	public function deliveryMethod(?string $value): string
	{
		return match ($value) {
			'courier' => 'Kurier',
			'locker' => 'Paczkomat',
			'pickup' => 'Odbiór osobisty',
			default => (string) $value,
		};
	}

	public function payuStatus(?string $value): string
	{
		return match ($value) {
			'NEW' => 'Nowa',
			'PENDING' => 'Oczekująca',
			'WAITING_FOR_CONFIRMATION' => 'Oczekuje na potwierdzenie',
			'COMPLETED' => 'Zakończona',
			'CANCELED' => 'Anulowana',
			default => (string) $value,
		};
	}
}
