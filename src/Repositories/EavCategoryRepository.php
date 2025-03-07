<?php

namespace Ufo\EAV\Repositories;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ufo\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Ufo\DoctrineBehaviors\ORM\Tree\TreeTrait;
use Ufo\EAV\Entity\EavCategory;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

abstract class EavCategoryRepository extends ServiceEntityRepository
{
    use TreeTrait;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function getById(int $id): EavCategory
    {
        return $this->findOneBy(['id' => $id]) ?? throw new EntityNotFoundException();
    }

    public function getBySlug(string $slug): EavCategory
    {
        return $this->findOneBy(['slug' => $slug]) ?? throw new EntityNotFoundException();
    }

    public function findCategoryByFilters(array $appliedFilters, ?string $locale = null): ?EavCategory
    {
        $category = null;
        $categories = $this->categoryByFilters($appliedFilters, $locale);
        foreach ($categories as $leafCategory) {
            $this->getTree($leafCategory->getRootMaterializedPath());
            $categoryFilters = $leafCategory->getFilters();
            $aggregateAppliedFilters = $this->aggregateAppliedFilters($appliedFilters);

            $isMatch = $this->isCategoryMatch($categoryFilters, $aggregateAppliedFilters);
            if ($isMatch) {
                $category = $leafCategory;
                $category->setCurrentLocale($locale);
                break;
            }
        }
        return $category;
    }

    function isCategoryMatch(array $categoryFilters, array $aggregateAppliedFilters): bool
    {
        foreach ($categoryFilters as $key => $values) {
            if (!isset($aggregateAppliedFilters[$key])) {
                return false;
            }

            if ($aggregateAppliedFilters[$key] !== $values) {
                return false;
            }
        }

        return true;
    }

    public function aggregateAppliedFilters(array $appliedFilters): array
    {
        $filters = [];
        foreach ($appliedFilters as $filter) {
            $filters[$filter->paramTag][] = $filter->value;
            $filters[$filter->paramTag] = array_unique($filters[$filter->paramTag]);
        }
        return $filters;
    }

    protected function categoryByFilters(array $appliedFilters, ?string $locale = null): array
    {
        $qb = $this->createQueryBuilder('c');

        // Додаємо мультимовність (тільки якщо локаль передана)
        if ($locale) {
            $qb->leftJoin('c.translations', 'ct', 'WITH', 'ct.locale = :locale')
               ->setParameter('locale', $locale);
        }

        foreach ($appliedFilters as $index => $filter) {
            $jsonValue = json_encode([$filter->paramTag => [$filter->value]], JSON_UNESCAPED_UNICODE);

            // Якщо є локалізовані фільтри – шукаємо і в `ct.filters`
            if ($locale !== null) {
                $qb->orWhere(
                    "(JSON_CONTAINS(c.filters, :filter{$index}) = 1 OR JSON_CONTAINS(ct.filters, :filter{$index}) = 1)"
                );
            } else {
                $qb->orWhere("JSON_CONTAINS(c.filters, :filter{$index}) = 1");
            }

            $qb->setParameter("filter{$index}", $jsonValue);
        }

        $qb->addSelect('COUNT_SLASHES(c.materializedPath) AS HIDDEN depth')
           ->orderBy('depth', 'DESC');

        return $qb->getQuery()->getResult();
    }

    protected function addFlatTreeConditions(QueryBuilder $queryBuilder, array $extraParams): void
    {
        $alias = $queryBuilder->getRootAliases()[0] ?? 't';
        if (isset($extraParams['orderBy']) && is_array($extraParams['orderBy'])) {
            foreach ($extraParams['orderBy'] as $field => $direction) {
                $queryBuilder->addOrderBy( $alias . '.' . $field, $direction);
            }
        }
    }
}
