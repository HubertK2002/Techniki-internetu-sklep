<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Order;

final class CartService
{
	public const COOKIE_NAME = 'cart_sid';
	public const COOKIE_TTL_DAYS = 30;

	private ?Cookie $cookieToSet = null;

	public function __construct(
		private readonly EntityManagerInterface $em,
		private readonly Security $security,
		private readonly RequestStack $requestStack,
	) {}

	public function applyCookie(Response $response): void
	{
		if ($this->cookieToSet instanceof Cookie) {
			$response->headers->setCookie($this->cookieToSet);
		}
	}

	public function getCurrentCart(bool $createIfMissing = true): ?Cart
	{
		$user = $this->getUser();

		if ($user) {
			$cart = $this->findActiveCartByUser($user);
			if ($cart) {
				return $cart;
			}

			if (!$createIfMissing) {
				return null;
			}

			$cart = new Cart();
			$cart->setUser($user);
			$cart->setStatus('active');

			$this->em->persist($cart);
			$this->em->flush();

			return $cart;
		}

		// guest
		$token = $this->getOrCreateGuestToken();
		$cart = $this->findActiveCartBySessionToken($token);

		if ($cart) {
			return $cart;
		}

		if (!$createIfMissing) {
			return null;
		}

		$cart = new Cart();
		$cart->setSessionToken($token);
		$cart->setStatus('active');

		$this->em->persist($cart);
		$this->em->flush();

		return $cart;
	}

	public function addProduct(int $productId, int $qty = 1): Cart
	{
		$qty = max(1, $qty);

		$cart = $this->getCurrentCart(true);
		if (!$cart) {
			throw new \RuntimeException('Nie udało się uzyskać koszyka.');
		}

		$product = $this->em->getRepository(Product::class)->find($productId);
		if (!$product) {
			throw new \InvalidArgumentException('Nie znaleziono produktu.');
		}

		$item = $this->findItem($cart, $product);
		if ($item) {
			$item->increaseQuantity($qty);
		} else {
			$item = new CartItem();
			$item->setCart($cart);
			$item->setProduct($product);
			$item->setQuantity($qty);

			$this->em->persist($item);
			$cart->addItem($item);
		}

		$cart->touch();
		$this->em->flush();

		return $cart;
	}

	public function setQuantity(int $productId, int $qty): Cart
	{
		$qty = max(1, $qty);

		$cart = $this->getCurrentCart(true);
		if (!$cart) {
			throw new \RuntimeException('Nie udało się uzyskać koszyka.');
		}

		$product = $this->em->getRepository(Product::class)->find($productId);
		if (!$product) {
			throw new \InvalidArgumentException('Nie znaleziono produktu.');
		}

		$item = $this->findItem($cart, $product);
		if (!$item) {
			// jeśli nie było pozycji, tworzymy
			$item = new CartItem();
			$item->setCart($cart);
			$item->setProduct($product);
			$this->em->persist($item);
			$cart->addItem($item);
		}

		$item->setQuantity($qty);

		$cart->touch();
		$this->em->flush();

		return $cart;
	}

	public function removeProduct(int $productId): Cart
	{
		$cart = $this->getCurrentCart(true);
		if (!$cart) {
			throw new \RuntimeException('Nie udało się uzyskać koszyka.');
		}

		$product = $this->em->getRepository(Product::class)->find($productId);
		if (!$product) {
			throw new \InvalidArgumentException('Nie znaleziono produktu.');
		}

		$item = $this->findItem($cart, $product);
		if ($item) {
			$cart->removeItem($item);
			$this->em->remove($item);

			$cart->touch();
			$this->em->flush();
		}

		return $cart;
	}

	public function attachToUserAndMerge(User $user): Cart
	{
		$userCart = $this->findActiveCartByUser($user);
		if (!$userCart) {
			$userCart = new Cart();
			$userCart->setUser($user);
			$userCart->setStatus('active');
			$this->em->persist($userCart);
		}

		$guestToken = $this->getGuestTokenFromCookie();
		if ($guestToken) {
			$guestCart = $this->findActiveCartBySessionToken($guestToken);

			if ($guestCart && $guestCart->getId() !== $userCart->getId()) {
				// merge pozycji
				foreach ($guestCart->getItems() as $guestItem) {
					$product = $guestItem->getProduct();
					if (!$product) {
						continue;
					}

					$existing = $this->findItem($userCart, $product);
					if ($existing) {
						$existing->increaseQuantity($guestItem->getQuantity());
					} else {
						$new = new CartItem();
						$new->setCart($userCart);
						$new->setProduct($product);
						$new->setQuantity($guestItem->getQuantity());

						$this->em->persist($new);
						$userCart->addItem($new);
					}
				}

				$guestCart->setStatus('merged');
				$guestCart->setSessionToken(null); 
				$this->em->flush();
			}
		}

		$userCart->touch();
		$this->em->flush();

		return $userCart;
	}

	private function getUser(): ?User
	{
		$u = $this->security->getUser();
		return $u instanceof User ? $u : null;
	}

	private function getOrCreateGuestToken(): string
	{
		$token = $this->getGuestTokenFromCookie();
		if ($token) {
			return $token;
		}

		$token = bin2hex(random_bytes(32));
		$this->cookieToSet = Cookie::create(self::COOKIE_NAME)
			->withValue($token)
			->withExpires(strtotime('+'.self::COOKIE_TTL_DAYS.' days'))
			->withPath('/')
			->withSecure($this->isSecureRequest())
			->withHttpOnly(true)
			->withSameSite('Lax');

		return $token;
	}

	private function getGuestTokenFromCookie(): ?string
	{
		$request = $this->requestStack->getCurrentRequest();
		if (!$request) {
			return null;
		}

		$token = (string) $request->cookies->get(self::COOKIE_NAME, '');
		$token = trim($token);

		return $token !== '' ? $token : null;
	}

	private function isSecureRequest(): bool
	{
		$request = $this->requestStack->getCurrentRequest();
		return $request ? $request->isSecure() : false;
	}

	private function findActiveCartByUser(User $user): ?Cart
	{
		return $this->em->getRepository(Cart::class)->findOneBy([
			'User' => $user,
			'Status' => 'active',
		]);
	}

	private function findActiveCartBySessionToken(string $token): ?Cart
	{
		return $this->em->getRepository(Cart::class)->findOneBy([
			'SessionToken' => $token,
			'Status' => 'active',
		]);
	}

	private function findItem(Cart $cart, Product $product): ?CartItem
	{
		return $this->em->getRepository(CartItem::class)->findOneBy([
			'Cart' => $cart,
			'Product' => $product,
		]);
	}

	public function checkout(): Order
{
	$cart = $this->getCurrentCart(false);
	if (!$cart || $cart->getItems()->count() === 0) {
		throw new \RuntimeException('Koszyk jest pusty.');
	}

	// 1) utwórz zamówienie powiązane z koszykiem
	$order = new Order();
	$order->setCart($cart);
	$order->setStatus('new');

	$user = $this->getUser();
	if ($user) {
		$order->setUser($user);
	}

	$this->em->persist($order);

	// 2) zamroź koszyk
	$cart->setStatus('ordered');

	// 3) UWAGA: jeśli SessionToken jest UNIQUE, zwolnij go na zamrożonym koszyku
	// żeby dało się stworzyć nowy koszyk dla gościa.
	if ($cart->getSessionToken()) {
		$cart->setSessionToken(null);
	}

	$this->em->flush();

	// 4) utwórz NOWY koszyk aktywny do dalszych zakupów
	$newCart = new Cart();
	$newCart->setStatus('active');

	if ($user) {
		$newCart->setUser($user);
	} else {
		// nowy token + ustaw cookie
		$token = bin2hex(random_bytes(32));
		$newCart->setSessionToken($token);

		$this->cookieToSet = Cookie::create(self::COOKIE_NAME)
			->withValue($token)
			->withExpires(strtotime('+'.self::COOKIE_TTL_DAYS.' days'))
			->withPath('/')
			->withSecure($this->isSecureRequest())
			->withHttpOnly(true)
			->withSameSite('Lax');
	}

	$this->em->persist($newCart);
	$this->em->flush();

	return $order;
}
}
