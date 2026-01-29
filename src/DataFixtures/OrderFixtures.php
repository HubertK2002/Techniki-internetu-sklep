<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class OrderFixtures extends Fixture implements DependentFixtureInterface
{
	public function load(ObjectManager $manager): void
	{
		$faker = Factory::create('pl_PL');

		/** @var User[] $users */
		$users = $manager->getRepository(User::class)->findAll();

		/** @var Product[] $products */
		$products = $manager->getRepository(Product::class)->findAll();

		if (!$users || !$products) {
			throw new \RuntimeException('Brak użytkowników lub produktów – najpierw uruchom UserFixtures i ProductFixtures.');
		}

		// ile zamówień łącznie (losowo rozkładamy na userów)
		$ordersTotal = 80;

		for ($i = 1; $i <= $ordersTotal; $i++) {
			$user = $users[array_rand($users)];

			// zamrożony koszyk jako snapshot zamówienia
			$cart = new Cart();
			$cart->setUser($user);
			$cart->setStatus('ordered');
			$cart->setSessionToken(null); // ważne (unikalne tokeny nie będą przeszkadzać)
			$manager->persist($cart);

			// pozycje koszyka
			$itemsCount = random_int(1, 6);

			$used = [];
			for ($j = 0; $j < $itemsCount; $j++) {
				$p = $products[array_rand($products)];
				if (isset($used[$p->getId()])) {
					continue;
				}
				$used[$p->getId()] = true;

				$item = new CartItem();
				$item->setCart($cart);
				$item->setProduct($p);
				$item->setQuantity(random_int(1, 4));

				$manager->persist($item);
				$cart->addItem($item);
			}

			$order = new Order();
			$order->setUser($user);
			$order->setCart($cart);

			// dostawa: courier/pickup
			$delivery = $faker->randomElement(['courier', 'pickup']);
			$order->setDeliveryMethod($delivery);

			if ($delivery === 'courier') {
				$order->setAddressLine($faker->streetAddress());
				$order->setPostalCode($faker->postcode());
				$order->setCity($faker->city());
			} else {
				$order->setAddressLine(null);
				$order->setPostalCode(null);
				$order->setCity(null);
			}

			// płatność: payu/cod
			$payment = $faker->randomElement(['payu', 'cod']);
			$order->setPaymentMethod($payment);

			// statusy
			if ($payment === 'payu' && $faker->boolean(60)) {
				// część opłacona
				$order->setStatus('paid');
				$order->setPayuStatus('COMPLETED');
				$order->setPaidAt(new \DateTimeImmutable('-'.random_int(0, 30).' days'));
				$order->setPayuLastStatusCheckAt(new \DateTimeImmutable());
				$order->setPayuAttempt(1);
				$order->setPayuOrderId('SANDBOX_'.$faker->bothify('????????????????'));
				$order->setPayuRedirectUri(null);
			} else {
				// złożone, nieopłacone (albo COD)
				$order->setStatus('confirmed');
				$order->setPayuStatus($payment === 'payu' ? 'NEW' : null);
				$order->setPaidAt(null);
				$order->setPayuLastStatusCheckAt(null);
				$order->setPayuAttempt($payment === 'payu' ? 1 : 0);
				$order->setPayuOrderId($payment === 'payu' ? 'SANDBOX_'.$faker->bothify('????????????????') : null);
				$order->setPayuRedirectUri($payment === 'payu' ? null : null);
			}

			$manager->persist($order);
		}

		$manager->flush();
	}

	public function getDependencies(): array
	{
		return [
			UserFixtures::class,
			ProductFixtures::class,
		];
	}
}
