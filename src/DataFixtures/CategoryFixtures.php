<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class CategoryFixtures extends Fixture
{
	public const REF_PREFIX = 'cat_';

	public function load(ObjectManager $manager): void
	{
		$faker = Factory::create('pl_PL');
		$slugger = new AsciiSlugger('pl');

		$names = [
			'Elektronika', 'Dom i ogród', 'Komputery', 'Gaming', 'RTV',
			'AGD', 'Oświetlenie', 'Narzędzia', 'Akcesoria', 'Sport',
			'Motoryzacja', 'Biuro',
		];

		$i = 0;
		foreach ($names as $name) {
			$cat = new Category();
			$cat->setName($name);

			$slug = $slugger->slug($name)->lower()->toString();
			$cat->setSlug($slug);

			$manager->persist($cat);

			$this->addReference(self::REF_PREFIX.$i, $cat);
			$i++;
		}

		$manager->flush();
	}
}