<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_details')]
class ProductDetails
{
	#[ORM\Id]
	#[ORM\OneToOne(targetEntity: Product::class, inversedBy: 'Details')]
	#[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'TowId', nullable: false, onDelete: 'CASCADE')]
	private ?Product $Product = null;

	#[ORM\Column(name: 'promotion_enabled', type: Types::BOOLEAN, options: ['default' => false])]
	private bool $PromotionEnabled = false;

	#[ORM\Column(name: 'promotion_percent', nullable: true)]
	private ?int $PromotionPercent = null;

	#[ORM\Column(name: 'image', length: 255, nullable: true)]
	private ?string $Image = null;

	#[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
	private ?string $Description = null;

	#[ORM\Column(name: 'stock', nullable: true)]
	private ?int $Stock = null;

	public function __construct(?Product $Product = null)
	{
		if ($Product !== null) {
			$this->setProduct($Product);
		}
	}

	public function getProduct(): ?Product
	{
		return $this->Product;
	}

	public function setProduct(Product $Product): static
	{
		$this->Product = $Product;

		if ($Product->getDetails() !== $this) {
			$Product->setDetails($this);
		}

		return $this;
	}

	public function isPromotionEnabled(): bool
	{
		return $this->PromotionEnabled;
	}

	public function setPromotionEnabled(bool $PromotionEnabled): static
	{
		$this->PromotionEnabled = $PromotionEnabled;

		return $this;
	}

	public function getPromotionPercent(): ?int
	{
		return $this->PromotionPercent;
	}

	public function setPromotionPercent(?int $PromotionPercent): static
	{
		if ($PromotionPercent === null) {
			$this->PromotionPercent = null;
			return $this;
		}

		$this->PromotionPercent = max(0, min(99, $PromotionPercent));
		return $this;
	}

	public function getImage(): ?string
	{
		return $this->Image;
	}

	public function setImage(?string $Image): static
	{
		$this->Image = $Image;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->Description;
	}

	public function setDescription(?string $Description): static
	{
		$this->Description = $Description;

		return $this;
	}

	public function getStock(): ?int
	{
		return $this->Stock;
	}

	public function setStock(?int $Stock): static
	{
		$this->Stock = $Stock;

		return $this;
	}
}
