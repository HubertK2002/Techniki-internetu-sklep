<?php

namespace App\DataFixtures;

use App\Entity\Opinion;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class OpinionFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
	public function load(ObjectManager $manager): void
	{
		$faker = Factory::create('pl_PL');

		$products = $manager->getRepository(Product::class)->findAll();
		$users = $manager->getRepository(User::class)->findAll();

		if ($products === [] || $users === []) {
			return;
		}

		$opinionsToCreate = 30000;

		for ($i = 0; $i < $opinionsToCreate; $i++) {
			$opinion = new Opinion();
			$opinion->setProduct($products[array_rand($products)]);
			$opinion->setUser($users[array_rand($users)]);
			$roll = $faker->numberBetween(1, 100);
			$rating = match (true) {
				$roll <= 45 => 5,
				$roll <= 80 => 4,
				$roll <= 90 => 3,
				$roll <= 97 => 2,
				default => 1,
			};
			$opinion->setRating($rating);
			$opinion->setComment($faker->boolean(85) ? $faker->realText(220) : null);
			$opinion->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-12 months', 'now')));

			$manager->persist($opinion);
		}

		$manager->flush();
	}

	public function getDependencies(): array
	{
		return [
			UserFixtures::class,
			ProductFixtures::class,
		];
	}

	public static function getGroups(): array
	{
		return ['opinions'];
	}
}
