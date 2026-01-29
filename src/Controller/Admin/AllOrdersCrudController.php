<?php

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

final class AllOrdersCrudController extends OrderCrudController
{
	public function configureCrud(Crud $crud): Crud
	{
		return parent::configureCrud($crud)
			->setPageTitle(Crud::PAGE_INDEX, 'Wszystkie zamówienia');
	}

	protected function applyBaseFilters(QueryBuilder $qb): void {}
}
