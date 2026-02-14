<?php

namespace App\Entity;

use App\Repository\WishlistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WishlistRepository::class)]
#[ORM\Table(
	name: 'wishlist',
	uniqueConstraints: [
		new ORM\UniqueConstraint(name: 'uniq_wishlist_user', columns: ['user_id']),
	]
)]
class Wishlist
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\OneToOne(targetEntity: User::class, inversedBy: 'Wishlist')]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
	private ?User $User = null;

	/** @var Collection<int, Product> */
	#[ORM\ManyToMany(targetEntity: Product::class)]
	#[ORM\JoinTable(name: 'wishlist_product')]
	#[ORM\JoinColumn(name: 'wishlist_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
	#[ORM\InverseJoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
	private Collection $Products;

	public function __construct()
	{
		$this->Products = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUser(): ?User
	{
		return $this->User;
	}

	public function setUser(User $User): self
	{
		$this->User = $User;
		if ($User->getWishlist() !== $this) {
			$User->setWishlist($this);
		}

		return $this;
	}

	/** @return Collection<int, Product> */
	public function getProducts(): Collection
	{
		return $this->Products;
	}

	public function hasProduct(Product $product): bool
	{
		return $this->Products->contains($product);
	}

	public function addProduct(Product $product): self
	{
		if (!$this->Products->contains($product)) {
			$this->Products->add($product);
		}

		return $this;
	}

	public function removeProduct(Product $product): self
	{
		$this->Products->removeElement($product);
		return $this;
	}
}
