<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

	public function configureFields(string $pageName): iterable
	{
		yield TextField::new('Name', 'Nazwa');
		yield MoneyField::new('Price', 'Cena')->setCurrency('PLN');
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
