<?php

namespace Ufo\EAV\Fillers;


use Ufo\EAV\Filters\Abstraction\ICommonFilter;
use Ufo\EAV\Filters\CommonFilter;
use Ufo\EAV\Filters\FilterRow\FilterData;

use function array_slice;

class FilteredSpecsFiller extends AbstractFiller
{

    public function filterResult(FilterData $filterData): static
    {
        $this->specIds = $this->viewSpecDetail->getByFilter($filterData);
        $this->specDetails = $this->viewSpecDetail->findBy(['specId' => $this->specIds, 'paramFiltered' => true]);
        return $this;
    }

    public function getSpecs(int $limit, int $offset): array
    {
        $specIds = array_slice($this->specIds, $offset, $limit);
        return $this->specRepository->getBySpecIds($specIds);
    }

    public function getCommonFilters(): ICommonFilter
    {
        return new CommonFilter($this->specDetails);
    }
}