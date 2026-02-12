<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class CategoryFixtures extends Fixture
{
	public const REF_PREFIX = 'cat_';

	public function load(ObjectManager $manager): void
	{
		$slugger = new AsciiSlugger('pl');

		$roots = [
			'Elektronika',
			'Dom i ogród',
			'Komputery',
			'Gaming',
			'RTV',
			'AGD',
			'Oświetlenie',
			'Narzędzia',
			'Akcesoria',
			'Sport',
			'Motoryzacja',
			'Biuro',
		];

		// 1) ROOT categories
		foreach ($roots as $name) {
			$cat = $this->makeCategory($name, $slugger);
			$manager->persist($cat);

			$this->addReference($this->ref($cat->getSlug()), $cat);
		}

		$manager->flush();

		// 2) SUB categories (2 poziom)
		$tree = [
			'elektronika' => ['Smartfony', 'Tablety', 'Audio', 'Smart Home', 'Foto i kamera'],
			'komputery' => ['Laptopy', 'PC', 'Monitory', 'Podzespoły', 'Sieć i routery'],
			'gaming' => ['Konsole', 'Gry', 'Peryferia', 'Fotele gamingowe'],
			'rtv' => ['Telewizory', 'Soundbary', 'Projektory'],
			'agd' => ['Pralki', 'Lodówki', 'Zmywarki', 'Odkurzacze', 'Ekspresy do kawy'],
			'dom-i-ogrod' => ['Meble', 'Ogród', 'Dekoracje', 'Porządek i sprzątanie'],
			'oswietlenie' => ['Lampy sufitowe', 'Lampki biurkowe', 'LED', 'Oświetlenie zewnętrzne'],
			'narzedzia' => ['Elektronarzędzia', 'Narzędzia ręczne', 'Pomiarowe', 'Warsztat'],
			'akcesoria' => ['Kable i adaptery', 'Ładowarki', 'Powerbanki', 'Etui i szkła'],
			'sport' => ['Siłownia', 'Bieganie', 'Rowery', 'Turystyka'],
			'motoryzacja' => ['Car audio', 'Chemia i kosmetyki', 'Akumulatory', 'Opony i felgi'],
			'biuro' => ['Drukarki', 'Papier', 'Artykuły biurowe', 'Ergonomia'],
		];

		foreach ($tree as $parentSlug => $childrenNames) {
			/** @var Category $parent */
			$parent = $this->getReference($this->ref($parentSlug), Category::class);

			foreach ($childrenNames as $childName) {
				$child = $this->makeCategory($childName, $slugger);
				$child->setParent($parent);

				$manager->persist($child);
				$this->addReference($this->ref($child->getSlug()), $child);
			}
		}

		$manager->flush();

		// 3) (opcjonalnie) 3 poziom – przykładowe wnuki
		$thirdLevel = [
			'laptopy' => ['Gamingowe', 'Ultrabooki', '2w1'],
			'podzespoly' => ['Karty graficzne', 'Procesory', 'RAM', 'Dyski SSD'],
			'smartfony' => ['Android', 'iOS', 'Budżetowe', 'Flagowe'],
			'telewizory' => ['OLED', 'QLED', 'LED'],
		];

		foreach ($thirdLevel as $parentSlug => $childrenNames) {
			$parentSlug = $slugger->slug($parentSlug)->lower()->toString(); // safety
			// UWAGA: nasze slugi i tak są już w refach; to tylko gdybyś zmienił nazwy
			// Lepiej: przechowuj klucze już jako slugi (jak wyżej) i nie sluguj tu.
		}

		// Lepsza wersja bez slugowania kluczy:
		$thirdLevel = [
			'laptopy' => ['Gamingowe', 'Ultrabooki', '2w1'],
			'podzespoly' => ['Karty graficzne', 'Procesory', 'RAM', 'Dyski SSD'],
			'smartfony' => ['Android', 'iOS', 'Budżetowe', 'Flagowe'],
			'telewizory' => ['OLED', 'QLED', 'LED'],
		];

		foreach ($thirdLevel as $parentSlug => $childrenNames) {
			/** @var Category $parent */
			$parent = $this->getReference($this->ref($parentSlug), Category::class);

			foreach ($childrenNames as $childName) {
				$child = $this->makeCategory($childName, $slugger);
				$child->setParent($parent);

				$manager->persist($child);
			}
		}

		$manager->flush();
	}

	private function makeCategory(string $name, AsciiSlugger $slugger): Category
	{
		$cat = new Category();
		$cat->setName($name);
		$cat->setSlug($slugger->slug($name)->lower()->toString());
		return $cat;
	}

	private function ref(string $slug): string
	{
		return self::REF_PREFIX.$slug;
	}
}
