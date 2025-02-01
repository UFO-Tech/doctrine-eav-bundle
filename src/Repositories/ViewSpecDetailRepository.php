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
            $orX->add($qb->expr()->andX(
                $qb->expr()->eq('e.paramTag', ':paramTag' . $k),
                    $qb->expr()->andX(
                    $qb->expr()->in('e.value', ':values' . $k)
                )
            ));

            $qb->setParameter(':paramTag' . $k, $param->tag);
            $qb->setParameter(':values' . $k, $param->getValuesArray());
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