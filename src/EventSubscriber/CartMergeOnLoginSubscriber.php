<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\CartService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class CartMergeOnLoginSubscriber implements EventSubscriberInterface
{
	public function __construct(
		private readonly CartService $cartService,
	) {}

	public static function getSubscribedEvents(): array
	{
		return [
			LoginSuccessEvent::class => 'onLoginSuccess',
		];
	}

	public function onLoginSuccess(LoginSuccessEvent $event): void
	{
		$user = $event->getUser();
		if (!$user instanceof User) {
			return;
		}

		$this->cartService->attachToUserAndMerge($user);

		$response = $event->getResponse();
		if ($response) {
			$this->cartService->applyCookie($response);
		}
	}
}
