<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class PromotionCrudController extends ProductCrudController
{
	public static function getEntityFqcn(): string
	{
		return Product::class;
	}

	public function configureCrud(Crud $crud): Crud
	{
		return parent::configureCrud($crud)
			->setEntityLabelInSingular('Promocja')
			->setEntityLabelInPlural('Promocje')
			->setPageTitle(Crud::PAGE_INDEX, 'Zarzadzanie promocjami');
	}

	public function createIndexQueryBuilder(
		SearchDto $searchDto,
		EntityDto $entityDto,
		FieldCollection $fields,
		FilterCollection $filters
	): QueryBuilder {
		$qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

		return $qb
			->addOrderBy('entity.PromotionEnabled', 'DESC')
			->addOrderBy('entity.PromotionPercent', 'DESC');
	}

	public function configureFields(string $pageName): iterable
	{
		yield TextField::new('Name', 'Produkt');
		yield AssociationField::new('Category', 'Kategoria');
		yield MoneyField::new('Price', 'Cena bazowa')->setCurrency('PLN')->setStoredAsCents(false)->setNumDecimals(4);
		yield IntegerField::new('PromotionPercent', 'Rabat (%)');
		yield MoneyField::new('EffectivePrice', 'Cena po rabacie')->setCurrency('PLN')->setStoredAsCents(false)->setNumDecimals(2)->onlyOnIndex();
		yield BooleanField::new('PromotionEnabled', 'Aktywna');
	}
}
