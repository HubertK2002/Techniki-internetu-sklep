<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'kategoria')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'KatId', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'Nazwa', length: 40)]
    private ?string $Name = null;

	#[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
	#[ORM\JoinColumn(name: 'CentrKatId', referencedColumnName: 'KatId', onDelete: 'SET NULL', nullable: true)]
	private ?self $parent = null;

	#[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
	private Collection $children;

	public function __construct()
	{
		$this->children = new ArrayCollection();
	}

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return self::slugify($this->Name);
    }

    public function setSlug(string $Slug): static
    {
        return $this;
    }

	public function getParent(): ?self
	{
		return $this->parent;
	}

	public function setParent(?self $parent): static
	{
		$this->parent = $parent;
		return $this;
	}

	/** @return Collection<int, self> */
	public function getChildren(): Collection
	{
		return $this->children;
	}

	public function addChild(self $child): static
	{
		if (!$this->children->contains($child)) {
			$this->children->add($child);
			$child->setParent($this);
		}
		return $this;
	}

	public function removeChild(self $child): static
	{
		if ($this->children->removeElement($child)) {
			if ($child->getParent() === $this) {
				$child->setParent(null);
			}
		}
		return $this;
	}

	public function __toString(): string
	{
		return (string) $this->getName();
	}

	public static function slugify(?string $value): ?string
	{
		if ($value === null) {
			return null;
		}

		$value = trim($value);
		if ($value === '') {
			return null;
		}

		$transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
		if ($transliterated !== false) {
			$value = $transliterated;
		}

		$value = strtolower($value);
		$value = preg_replace('/\s+/u', ' ', $value) ?? $value;
		$value = trim($value);
		$value = preg_replace('/[^a-z0-9 ]+/', '', $value) ?? $value;
		$value = preg_replace('/ +/', '-', $value) ?? $value;
		$value = trim($value, '-');

		return $value !== '' ? $value : null;
	}
}
