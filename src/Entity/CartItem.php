<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
	name: 'cart_item',
	uniqueConstraints: [
		new ORM\UniqueConstraint(name: 'uniq_cart_product', columns: ['cart_id', 'product_id']),
	],
	indexes: [
		new ORM\Index(name: 'idx_cart_item_cart', columns: ['cart_id']),
		new ORM\Index(name: 'idx_cart_item_product', columns: ['product_id']),
	]
)]
class CartItem
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'Items')]
	#[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
	private ?Cart $Cart = null;

	#[ORM\ManyToOne(targetEntity: Product::class)]
	#[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
	private ?Product $Product = null;

	#[ORM\Column(name: 'quantity', type: 'integer')]
	private int $Quantity = 1;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getCart(): ?Cart
	{
		return $this->Cart;
	}

	public function setCart(?Cart $Cart): self
	{
		$this->Cart = $Cart;
		return $this;
	}

	public function getProduct(): ?Product
	{
		return $this->Product;
	}

	public function setProduct(?Product $Product): self
	{
		$this->Product = $Product;
		return $this;
	}

	public function getQuantity(): int
	{
		return $this->Quantity;
	}

	public function setQuantity(int $Quantity): self
	{
		$this->Quantity = max(1, $Quantity);
		return $this;
	}

	public function increaseQuantity(int $by = 1): self
	{
		$this->Quantity = max(1, $this->Quantity + $by);
		return $this;
	}

	public function decreaseQuantity(int $by = 1): self
	{
		$this->Quantity = max(1, $this->Quantity - $by);
		return $this;
	}
}
