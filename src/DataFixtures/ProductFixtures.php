<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Category;

final class ProductFixtures extends Fixture implements DependentFixtureInterface
{
	public function load(ObjectManager $manager): void
	{
		$faker = Factory::create('pl_PL');

		for ($i = 1; $i <= 4500; $i++) {
			$product = new Product();

			$product->setName($faker->unique()->words(mt_rand(2, 4), true));
			$product->setPrice($faker->randomFloat(2, 5, 999));
			$product->setStock($faker->numberBetween(0, 200));
			$product->setDescription($faker->paragraphs(mt_rand(1, 3), true));

			$categories = $manager->getRepository(Category::class)->findAll();
			if (!$categories) {
				throw new \RuntimeException('Brak kategorii w bazie.');
			}

			$category = $categories[array_rand($categories)];
			$product->setCategory($category);

			$product->setImage('product_'.($i % 300 + 1).'.jpg');

			$manager->persist($product);
		}

		$manager->flush();
	}

	public function getDependencies(): array
	{
		return [
			CategoryFixtures::class,
		];
	}
}
