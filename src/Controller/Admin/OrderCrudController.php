<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Service\OrderValueLabelService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class OrderCrudController extends AbstractCrudController
{
	public function __construct(private readonly OrderValueLabelService $labels) {}

	public static function getEntityFqcn(): string
	{
		return Order::class;
	}

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			->setEntityLabelInSingular('Zamówienie')
			->setEntityLabelInPlural('Zamówienia')
			->setDefaultSort(['id' => 'DESC'])
			->showEntityActionsInlined()
			->overrideTemplate('crud/detail', 'admin/order_detail.html.twig');
	}

	protected function applyBaseFilters(QueryBuilder $qb): void
	{
		// Domyślnie bez dodatkowych filtrów.
	}

	public function createIndexQueryBuilder(
		SearchDto $searchDto,
		EntityDto $entityDto,
		FieldCollection $fields,
		FilterCollection $filters
	): QueryBuilder {
		$qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
		$this->applyBaseFilters($qb);

		return $qb;
	}

	public function configureActions(Actions $actions): Actions
	{
		return $actions
			->disable(Action::NEW, Action::EDIT, Action::DELETE)
			->add(Crud::PAGE_INDEX, Action::DETAIL);
	}

	public function configureFields(string $pageName): iterable
	{
		yield IdField::new('id');
		yield AssociationField::new('User', 'Użytkownik');
		yield TextField::new('Status', 'Status')
			->formatValue(fn ($value) => $this->labels->status($value));
		yield TextField::new('PaymentMethod', 'Płatność')
			->formatValue(fn ($value) => $this->labels->paymentMethod($value));
		yield TextField::new('DeliveryMethod', 'Dostawa')
			->formatValue(fn ($value) => $this->labels->deliveryMethod($value));
		yield DateTimeField::new('CreatedAt', 'Utworzono');
	}

	public function configureFilters(Filters $filters): Filters
	{
		return $filters
			->add(TextFilter::new('Status'))
			->add(TextFilter::new('PaymentMethod'));
	}
}

