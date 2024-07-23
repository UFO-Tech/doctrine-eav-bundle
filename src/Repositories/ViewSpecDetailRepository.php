<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\EntityRepository;
use Ufo\EAV\Entity\Views\CommonParamsFilter;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Filters\FilterRow\FilterData;

use function array_column;
use function count;

class ViewSpecDetailRepository extends EntityRepository
{
    /**
     * @param string $queryString
     * @return SpecDetail[]
     */
    public function search(string $queryString): array
    {
        $queryBuilder = $this->createQueryBuilder('sd');

        $queryBuilder->select('sd', 's')
            ->leftJoin('sd.spec', 's')  // Додаємо join з Spec
            ->where('LOWER(sd.value) = :exactQuery')
            ->orWhere('LOWER(sd.value) LIKE :partialQuery')
            ->orderBy('CASE WHEN LOWER(sd.value) = :exactQuery THEN 0 ELSE 1 END')
            ->setParameter('exactQuery', strtolower($queryString))
            ->setParameter('partialQuery', '%' . strtolower($queryString) . '%');

        return $queryBuilder->getQuery()->getResult();
    }

    public function all(): array
    {
        return $this->findAll();
    }

    public function getByFilter(FilterData $filterData): array
    {
        $qb = $this->createQueryBuilder('e')
                   ->select('e.specId')
                   ->groupBy('e.specId');

        $orX = $qb->expr()->orX();

        foreach ($filterData->getParams() as $k => $param) {
            $exactValues = [];
            $likeConditions = $qb->expr()->orX();

            foreach ($param->getValuesArray() as $valueKey => $value) {
                $exactValues[] = $value;
                $likeConditions->add(
                    $qb->expr()->like('e.value', ':like_value' . $k . '_' . $valueKey)
                );
                $qb->setParameter(':like_value' . $k . '_' . $valueKey, '%' . $value . '%');
            }

            $orX->add($qb->expr()->andX(
                $qb->expr()->eq('e.paramTag', ':paramTag' . $k),
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->neq('e.valueType', ':valueType_options' . $k),
                        $qb->expr()->in('e.value', ':values' . $k)
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->eq('e.valueType', ':valueType_options' . $k),
                        $likeConditions
                    )
                )
            ));

            $qb->setParameter(':paramTag' . $k, $param->tag);
            $qb->setParameter(':values' . $k, $exactValues);
            $qb->setParameter(':valueType_options' . $k, 'options');

        }

        $qb->where($orX)
           ->having('COUNT(DISTINCT e.paramTag) = :paramCount')
           ->setParameter(':paramCount', count($filterData->getParams()));

        $result = $qb->getQuery()->getArrayResult();
        return array_column($result, 'specId');
    }

    public function experimentalIterator(): array
    {
        $iterator = $this->createQueryBuilder('v')->getQuery()->toIterable();

        $res = [];
        $i = 0;
        foreach ($iterator as $specDetail) {
            $i++;
            if ($i === 30000) {
                $i =0;
                $this->getEntityManager()->clear();
            }
            $res[] = $specDetail;
        }
        return $res;
    }

    public function test()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
             ->from(CommonParamsFilter::class, 'f');
        $q = $qb->getQuery()->toIterable();
        return $qb->getQuery()->getResult();
    }
}