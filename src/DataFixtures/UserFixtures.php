<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture
{
	public function __construct(
		private readonly UserPasswordHasherInterface $hasher,
	) {}

	public function load(ObjectManager $manager): void
	{
		$faker = Factory::create('pl_PL');

		$admins = [
			['email' => 'admin1@example.com', 'first' => 'Admin', 'last' => 'One'],
			['email' => 'admin2@example.com', 'first' => 'Admin', 'last' => 'Two'],
			['email' => 'admin3@example.com', 'first' => 'Admin', 'last' => 'Three'],
		];

		foreach ($admins as $a) {
			$user = new User();
			$user->setEmail($a['email']);
			$user->setFirstName($a['first']);
			$user->setLastName($a['last']);
			$user->setIsVerified(true);
			$user->setRoles(['ROLE_ADMIN']);
			$user->setPassword($this->hasher->hashPassword($user, 'admin1234'));

			$wishlist = new Wishlist();
			$wishlist->setUser($user);

			$manager->persist($user);
			$manager->persist($wishlist);
		}

		for ($i = 1; $i <= 30; $i++) {
			$user = new User();

			$user->setEmail($faker->unique()->safeEmail());
			$user->setFirstName($faker->firstName());
			$user->setLastName($faker->lastName());
			$user->setIsVerified(true);
			$user->setPassword($this->hasher->hashPassword($user, 'test1234'));

			$wishlist = new Wishlist();
			$wishlist->setUser($user);

			$manager->persist($user);
			$manager->persist($wishlist);
		}

		$manager->flush();
	}
}
