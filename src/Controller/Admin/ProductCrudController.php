<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

	public function configureActions(Actions $actions): Actions
	{
		return $actions
			->disable(Action::NEW, Action::DELETE);
	}

	public function configureFields(string $pageName): iterable
	{
		yield TextField::new('Name', 'Nazwa');
		yield MoneyField::new('Price', 'Cena')->setCurrency('PLN')->setStoredAsCents(false)->setNumDecimals(4);
		yield BooleanField::new('PromotionEnabled', 'Promocja aktywna');
		yield IntegerField::new('PromotionPercent', 'Rabat (%)')->setHelp('Wpisz wartość 1-99');
		yield IntegerField::new('Stock', 'Stan');
		yield TextEditorField::new('Description', 'Opis')->hideOnIndex();
		yield AssociationField::new('Category', 'Kategoria');

		yield ImageField::new('Image', 'Zdjęcie')
			->setBasePath('/products')
			->setUploadDir('public/products')
			->setUploadedFileNamePattern('[randomhash].[extension]')
			->setRequired(false)
			->setFormTypeOptions([
				'attr' => ['accept' => 'image/png,image/jpeg'],
			]);
	}
}
