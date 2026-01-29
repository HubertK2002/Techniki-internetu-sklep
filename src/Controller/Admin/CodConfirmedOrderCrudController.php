<?php

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

final class CodConfirmedOrderCrudController extends OrderCrudController
{
	public function configureCrud(Crud $crud): Crud
	{
		return parent::configureCrud($crud)
			->setPageTitle(Crud::PAGE_INDEX, 'Zamówienia COD (confirmed)');
	}

	protected function applyBaseFilters(QueryBuilder $qb): void
	{
		$qb->andWhere('entity.PaymentMethod = :pm')->setParameter('pm', 'cod');
		$qb->andWhere('entity.Status = :st')->setParameter('st', 'confirmed');
	}
}
