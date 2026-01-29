<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`order`')]
class Order
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne]
	#[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
	private ?User $User = null;

	#[ORM\OneToOne]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private ?Cart $Cart = null;

	#[ORM\Column(length: 20)]
	private string $Status = 'new';

	// Dostawa/płatność – na razie jako string (potem możesz zrobić enum)
	#[ORM\Column(length: 20, nullable: true)]
	private ?string $DeliveryMethod = null;// courier|locker|pickup

	#[ORM\Column(length: 20, nullable: true)]
	private ?string $PaymentMethod = null; // card|blik|transfer|cod

	// dla kuriera:
	#[ORM\Column(length: 120, nullable: true)]
	private ?string $AddressLine = null;

	#[ORM\Column(length: 20, nullable: true)]
	private ?string $PostalCode = null;

	#[ORM\Column(length: 80, nullable: true)]
	private ?string $City = null;

	// dla paczkomatu:
	#[ORM\Column(length: 40, nullable: true)]
	private ?string $LockerCode = null;

	#[ORM\Column(length: 20, nullable: true)]
	private ?string $LockerProvider = null; 

	// dla odbioru:
	#[ORM\Column(length: 120, nullable: true)]
	private ?string $PickupLocation = null;

	#[ORM\Column]
	private \DateTimeImmutable $CreatedAt;

	#[ORM\Column(length: 64, nullable: true)]
	private ?string $PayuOrderId = null;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $PayuRedirectUri = null;

	#[ORM\Column(length: 30, nullable: true)]
	private ?string $PayuStatus = null;

	#[ORM\Column(type: 'datetime_immutable', nullable: true)]
	private ?\DateTimeImmutable $PaidAt = null;

	#[ORM\Column(type: 'datetime_immutable', nullable: true)]
	private ?\DateTimeImmutable $PayuLastStatusCheckAt = null;

	#[ORM\Column]
	private int $PayuAttempt = 0;

	public function __construct()
	{
		$this->CreatedAt = new \DateTimeImmutable();
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

	public function getCart(): ?Cart
	{
		return $this->Cart;
	}

	public function setCart(Cart $Cart): self
	{
		$this->Cart = $Cart;
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

	public function getDeliveryMethod(): ?string
	{
		return $this->DeliveryMethod;
	}

	public function setDeliveryMethod(?string $DeliveryMethod): self
	{
		$this->DeliveryMethod = $DeliveryMethod;
		return $this->touch();
	}

	public function getPaymentMethod(): ?string
	{
		return $this->PaymentMethod;
	}

	public function setPaymentMethod(?string $PaymentMethod): self
	{
		$this->PaymentMethod = $PaymentMethod;
		return $this->touch();
	}

	public function getAddressLine(): ?string
	{
		return $this->AddressLine;
	}

	public function setAddressLine(?string $AddressLine): self
	{
		$this->AddressLine = $AddressLine;
		return $this->touch();
	}

	public function getPostalCode(): ?string
	{
		return $this->PostalCode;
	}

	public function setPostalCode(?string $PostalCode): self
	{
		$this->PostalCode = $PostalCode;
		return $this->touch();
	}

	public function getCity(): ?string
	{
		return $this->City;
	}

	public function setCity(?string $City): self
	{
		$this->City = $City;
		return $this->touch();
	}

	public function getLockerCode(): ?string
	{
		return $this->LockerCode;
	}

	public function setLockerCode(?string $LockerCode): self
	{
		$this->LockerCode = $LockerCode;
		return $this->touch();
	}

	public function getPickupLocation(): ?string
	{
		return $this->PickupLocation;
	}

	public function setPickupLocation(?string $PickupLocation): self
	{
		$this->PickupLocation = $PickupLocation;
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

	public function getPayuOrderId(): ?string
	{
		return $this->PayuOrderId;
	}

	public function setPayuOrderId(?string $PayuOrderId): self
	{
		$this->PayuOrderId = $PayuOrderId;
		return $this->touch();
	}

	public function getPayuRedirectUri(): ?string
	{
		return $this->PayuRedirectUri;
	}

	public function setPayuRedirectUri(?string $PayuRedirectUri): self
	{
		$this->PayuRedirectUri = $PayuRedirectUri;
		return $this->touch();
	}

	public function getPayuStatus(): ?string
	{
		return $this->PayuStatus;
	}

	public function setPayuStatus(?string $PayuStatus): self
	{
		$this->PayuStatus = $PayuStatus;
		return $this->touch();
	}

	public function getPaidAt(): ?\DateTimeImmutable
	{
		return $this->PaidAt;
	}

	public function setPaidAt(?\DateTimeImmutable $PaidAt): self
	{
		$this->PaidAt = $PaidAt;
		return $this->touch();
	}

	public function getPayuLastStatusCheckAt(): ?\DateTimeImmutable
	{
		return $this->PayuLastStatusCheckAt;
	}

	public function setPayuLastStatusCheckAt(?\DateTimeImmutable $PayuLastStatusCheckAt): self
	{
		$this->PayuLastStatusCheckAt = $PayuLastStatusCheckAt;
		return $this->touch();
	}

	public function isPaid(): bool
	{
		return $this->PaidAt !== null || $this->Status === 'paid';
	}

	public function canContinuePayu(): bool
	{
		return $this->PaymentMethod === 'payu' && !$this->isPaid() && $this->PayuRedirectUri !== null;
	}

	public function getPayuAttempt(): int
	{
		return $this->PayuAttempt;
	}

	public function setPayuAttempt(int $PayuAttempt): self
	{
		$this->PayuAttempt = max(0, $PayuAttempt);
		return $this->touch();
	}

	public function increasePayuAttempt(): self
	{
		$this->PayuAttempt++;
		return $this->touch();
	}

}
