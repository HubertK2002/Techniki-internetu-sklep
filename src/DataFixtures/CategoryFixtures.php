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

	public const NAMES = [
		'Elektronika', 'Dom i ogród', 'Komputery', 'Gaming', 'RTV',
		'AGD', 'Oświetlenie', 'Narzędzia', 'Akcesoria', 'Sport',
		'Motoryzacja', 'Biuro',
	];

	public function load(ObjectManager $manager): void
	{
		$faker = Factory::create('pl_PL');
		$slugger = new AsciiSlugger('pl');

		$created = [];
		foreach (self::NAMES as $i => $name) {
			$cat = new Category();
			$cat->setName($name);

			$slug = $slugger->slug($name)->lower()->toString();
			$cat->setSlug($slug);

			$manager->persist($cat);
			$created[$i] = $cat;
		}

		$manager->flush(); 

		foreach ($created as $i => $cat) {
			$this->addReference(self::REF_PREFIX.$i, $cat);
		}
	}
}