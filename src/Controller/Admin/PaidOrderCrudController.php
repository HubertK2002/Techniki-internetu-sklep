<?php

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

final class PaidOrderCrudController extends OrderCrudController
{
	public function configureCrud(Crud $crud): Crud
	{
		return parent::configureCrud($crud)
			->setPageTitle(Crud::PAGE_INDEX, 'Zamówienia opłacone');
	}

	protected function applyBaseFilters(QueryBuilder $qb): void
	{
		$qb->andWhere('entity.Status = :st')->setParameter('st', 'paid');
	}
}
