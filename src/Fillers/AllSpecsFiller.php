<?php

namespace Ufo\EAV\Fillers;

use Ufo\EAV\Entity\Views\CommonParamsFilter;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Filters\Abstraction\ICommonFilter;
use Ufo\EAV\Filters\AllCommonFilter;
use Ufo\EAV\Filters\FilterRow\FilterData;

class AllSpecsFiller extends AbstractFiller
{
    protected array $criteria = [];

    public function filterResult(FilterData $filterData): static
    {
//        $this->specDetails = $this->viewSpecDetail->findBy([], limit: 50000);
//        $this->specDetails = $this->viewSpecDetail->experimentalIterator();
//        $this->specDetails = $this->em->getRepository(CommonParamsFilter::class)->findAll();

//        array_reduce($this->specDetails, function($carry, SpecDetail $specDetail) {
//            $specId = $specDetail->getSpec()->getId();
//            if (!in_array($specId, $carry)) {
//                $carry[] = $specId;
//            }
//            return $carry;
//        }, $this->specIds);
        return $this;
    }

    public function getSpecs(int $limit, int $offset, array $criteria = [], ?array $orderBy = null): array
    {
        $this->criteria = $criteria;
        return $this->specRepository->getList($criteria, $orderBy, $limit, $offset);
    }

    public function getCommonFilters(): ICommonFilter
    {
        $count = $this->specRepository->count($this->criteria);
        $commonParams = $this->em->getRepository(CommonParamsFilter::class)->findAll();
        return new AllCommonFilter($commonParams, $count);
    }

}