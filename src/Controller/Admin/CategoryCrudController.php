<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Doctrine\ORM\QueryBuilder;

class CategoryCrudController extends AbstractCrudController
{
	public function __construct(
		private CategoryRepository $categoryRepository,
	) {}

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
		yield TextField::new('Name', 'Nazwa');
		yield TextField::new('Slug', 'Slug')->hideOnIndex();
		yield $this->parentField();
    }
    
	private function parentField(): AssociationField
	{
		$excludedIds = [];

		$context = $this->getContext();
		$instance = $context?->getEntity()?->getInstance();

		if ($instance instanceof Category && $instance->getId() !== null) {
			$excludedIds = array_merge(
				[(int) $instance->getId()],
				$this->categoryRepository->findDescendantIds($instance)
			);
		}

		$field = AssociationField::new('parent', 'Rodzic')
			->setRequired(false);

		if ($excludedIds) {
			$field->setQueryBuilder(function (QueryBuilder $qb) use ($excludedIds) {
				return $qb
					->andWhere($qb->expr()->notIn('entity.id', ':excluded'))
					->setParameter('excluded', $excludedIds)
					->orderBy('entity.Name', 'ASC');
			});
		}

		return $field;
	}
}
