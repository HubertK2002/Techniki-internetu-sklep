<?php

namespace App\Entity;

use App\Repository\OpinionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OpinionRepository::class)]
#[ORM\Table(name: 'opinion')]
class Opinion
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(inversedBy: 'Opinions')]
	#[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'TowId', nullable: false, onDelete: 'CASCADE')]
	private ?Product $Product = null;

	#[ORM\ManyToOne(inversedBy: 'Opinions')]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
	private ?User $User = null;

	#[ORM\Column(name: 'rating', type: Types::SMALLINT)]
	#[Assert\Range(min: 1, max: 5)]
	private ?int $Rating = null;

	#[ORM\Column(name: 'comment', type: Types::TEXT, nullable: true)]
	private ?string $Comment = null;

	#[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
	private ?\DateTimeImmutable $CreatedAt = null;

	public function __construct()
	{
		$this->CreatedAt = new \DateTimeImmutable();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getProduct(): ?Product
	{
		return $this->Product;
	}

	public function setProduct(?Product $Product): static
	{
		$this->Product = $Product;

		return $this;
	}

	public function getUser(): ?User
	{
		return $this->User;
	}

	public function setUser(?User $User): static
	{
		$this->User = $User;

		return $this;
	}

	public function getRating(): ?int
	{
		return $this->Rating;
	}

	public function setRating(int $Rating): static
	{
		$this->Rating = $Rating;

		return $this;
	}

	public function getComment(): ?string
	{
		return $this->Comment;
	}

	public function setComment(?string $Comment): static
	{
		$this->Comment = $Comment;

		return $this;
	}

	public function getCreatedAt(): ?\DateTimeImmutable
	{
		return $this->CreatedAt;
	}

	public function setCreatedAt(\DateTimeImmutable $CreatedAt): static
	{
		$this->CreatedAt = $CreatedAt;

		return $this;
	}
}
