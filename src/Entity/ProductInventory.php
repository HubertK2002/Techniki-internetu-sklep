<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'istw')]
class ProductInventory
{
	#[ORM\Id]
	#[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'Inventories')]
	#[ORM\JoinColumn(name: 'TowId', referencedColumnName: 'TowId', nullable: false)]
	private ?Product $Product = null;

	#[ORM\Id]
	#[ORM\Column(name: 'MagId', type: Types::INTEGER)]
	private ?int $MagId = null;

	#[ORM\Column(name: 'StanMag', type: Types::DECIMAL, precision: 15, scale: 4)]
	private string $Stock = '0.0000';

	#[ORM\Column(name: 'RezerwacjaMag', type: Types::DECIMAL, precision: 15, scale: 4, nullable: true)]
	private ?string $ReservedStock = null;

	public function getProduct(): ?Product
	{
		return $this->Product;
	}

	public function getMagId(): ?int
	{
		return $this->MagId;
	}

	public function getAvailableStock(): float
	{
		return (float) $this->Stock - (float) ($this->ReservedStock ?? 0);
	}
}
