<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
	name: 'cart',
	indexes: [
		new ORM\Index(name: 'idx_cart_session', columns: ['session_token']),
		new ORM\Index(name: 'idx_cart_status', columns: ['status']),
	]
)]
class Cart
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
	private ?User $User = null;

	#[ORM\Column(name: 'session_token', type: 'string', length: 64, nullable: true, unique: true)]
	private ?string $SessionToken = null;

	#[ORM\Column(name: 'status', type: 'string', length: 20)]
	private string $Status = 'active';

	#[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
	private \DateTimeImmutable $CreatedAt;

	#[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
	private \DateTimeImmutable $UpdatedAt;

	/** @var Collection<int, CartItem> */
	#[ORM\OneToMany(
		mappedBy: 'Cart',
		targetEntity: CartItem::class,
		orphanRemoval: true,
		cascade: ['persist', 'remove']
	)]
	private Collection $Items;

	public function __construct()
	{
		$this->Items = new ArrayCollection();

		$now = new \DateTimeImmutable();
		$this->CreatedAt = $now;
		$this->UpdatedAt = $now;
	}

	public function touch(): self
	{
		$this->UpdatedAt = new \DateTimeImmutable();
		return $this;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUser(): ?User
	{
		return $this->User;
	}

	public function setUser(?User $User): self
	{
		$this->User = $User;
		return $this->touch();
	}

	public function getSessionToken(): ?string
	{
		return $this->SessionToken;
	}

	public function setSessionToken(?string $SessionToken): self
	{
		$this->SessionToken = $SessionToken;
		return $this->touch();
	}

	public function getStatus(): string
	{
		return $this->Status;
	}

	public function setStatus(string $Status): self
	{
		$this->Status = $Status;
		return $this->touch();
	}

	public function getCreatedAt(): \DateTimeImmutable
	{
		return $this->CreatedAt;
	}

	public function setCreatedAt(\DateTimeImmutable $CreatedAt): self
	{
		$this->CreatedAt = $CreatedAt;
		return $this;
	}

	public function getUpdatedAt(): \DateTimeImmutable
	{
		return $this->UpdatedAt;
	}

	public function setUpdatedAt(\DateTimeImmutable $UpdatedAt): self
	{
		$this->UpdatedAt = $UpdatedAt;
		return $this;
	}

	/** @return Collection<int, CartItem> */
	public function getItems(): Collection
	{
		return $this->Items;
	}

	public function addItem(CartItem $item): self
	{
		if (!$this->Items->contains($item)) {
			$this->Items->add($item);
			$item->setCart($this);
			$this->touch();
		}

		return $this;
	}

	public function removeItem(CartItem $item): self
	{
		if ($this->Items->removeElement($item)) {
			if ($item->getCart() === $this) {
				$item->setCart(null);
			}
			$this->touch();
		}

		return $this;
	}
}
