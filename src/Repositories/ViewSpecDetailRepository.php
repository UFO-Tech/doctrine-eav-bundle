<?php

namespace Ufo\EAV\Repositories;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Filters\FilterRow\FilterData;
use Ufo\EAV\Services\LocaleService;

use function array_column;
use function count;

class ViewSpecDetailRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected LocaleService $localeService
    )
    {
        parent::__construct($registry, SpecDetail::class);
    }

    public function findAll(): array
    {
        return $this->findBy($this->localeService->getLocaleCriteria());
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return parent::findBy($this->localeService->addLocaleToCriteria($criteria), $orderBy, $limit, $offset);
    }

    public function getByFilter(FilterData $filterData): array
    {
        $locale = $this->localeService->getLocale();
        $isDefaultLocale = $this->localeService->isDefaultLocale();

        $qb = $this->createQueryBuilder('e')
                   ->select('e.specId')
                   ->groupBy('e.specId');

        $orX = $qb->expr()->orX();

        foreach ($filterData->getParams() as $k => $param) {
            $orX->add($qb->expr()->andX(
                $qb->expr()->eq('e.paramTag', ':paramTag' . $k),
                $qb->expr()->in('e.value', ':values' . $k)
            ));

            $qb->setParameter(':paramTag' . $k, $param->tag);
            $qb->setParameter(':values' . $k, $param->getValuesArray());
        }

        $qb->where($orX)
           ->having('COUNT(DISTINCT e.paramTag) = :paramCount')
           ->setParameter(':paramCount', count($filterData->getParams()));

        // Додаємо облік локалізації
        if (!$isDefaultLocale) {
            $qb->andWhere('(e.locale = :locale OR e.locale IS NULL)')
               ->setParameter('locale', $locale);
        } else {
            $qb->andWhere('e.locale IS NULL');
        }

        $result = $qb->getQuery()->getArrayResult();
        return array_column($result, 'specId');
    }

}
