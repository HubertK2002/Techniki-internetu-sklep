<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $Price = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $PromotionEnabled = false;

    #[ORM\Column(nullable: true)]
    private ?int $PromotionPercent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Description = null;

    #[ORM\Column(nullable: true)]
    private ?int $Stock = null;

    #[ORM\Column(length: 100)]
    private ?string $Name = null;

    #[ORM\ManyToOne]
    private ?Category $Category = null;

	/** @var Collection<int, Opinion> */
	#[ORM\OneToMany(mappedBy: 'Product', targetEntity: Opinion::class, orphanRemoval: true)]
	private Collection $Opinions;

	public function __construct()
	{
		$this->Opinions = new ArrayCollection();
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?float
    {
        return $this->Price;
    }

    public function setPrice(float $Price): static
    {
        $this->Price = $Price;

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

	public function hasPromotion(): bool
	{
		return $this->PromotionEnabled
			&& $this->PromotionPercent !== null
			&& $this->PromotionPercent > 0;
	}

	public function getEffectivePrice(): float
	{
		$basePrice = (float) ($this->Price ?? 0);

		if (!$this->hasPromotion()) {
			return $basePrice;
		}

		$discounted = $basePrice * (100 - (int) $this->PromotionPercent) / 100;
		return round($discounted, 2);
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

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->Category;
    }

    public function setCategory(?Category $Category): static
    {
        $this->Category = $Category;

        return $this;
    }

	/** @return Collection<int, Opinion> */
	public function getOpinions(): Collection
	{
		return $this->Opinions;
	}

	public function addOpinion(Opinion $Opinion): static
	{
		if (!$this->Opinions->contains($Opinion)) {
			$this->Opinions->add($Opinion);
			$Opinion->setProduct($this);
		}

		return $this;
	}

	public function removeOpinion(Opinion $Opinion): static
	{
		if ($this->Opinions->removeElement($Opinion)) {
			if ($Opinion->getProduct() === $this) {
				$Opinion->setProduct(null);
			}
		}

		return $this;
	}
}
