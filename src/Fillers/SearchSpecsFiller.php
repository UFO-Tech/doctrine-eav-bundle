<?php

namespace Ufo\EAV\Fillers;


use Doctrine\ORM\EntityManagerInterface;
use Ufo\EAV\Entity\Views\SpecDetailsJson;
use Ufo\EAV\Filters\Abstraction\ICommonFilter;
use Ufo\EAV\Filters\FilterRow\FilterData;
use Ufo\EAV\Filters\SearchCommonFilter;
use Ufo\EAV\Services\SearchService;

use function array_map;
use function array_slice;

class SearchSpecsFiller extends AbstractFiller
{
    public function __construct(
        EntityManagerInterface $em,
        protected SearchService $searchService
    )
    {
        parent::__construct($em);
    }

    public function filterResult(FilterData $filterData): static
    {
        $query = current($filterData->getParam('query')->getValues())->content;
        $filterData->exclude('query');
        $this->specDetails =  $this->searchService->search($query, $filterData);
        $this->specIds = array_map(function (SpecDetailsJson $specDetails) {
            return $specDetails->getSpecId();
        }, $this->specDetails);
        return $this;
    }

    public function getSpecs(int $limit, int $offset): array
    {
        $specIds = array_slice($this->specIds, $offset, $limit);
        return $this->specRepository->getBySpecIds($specIds);
    }

    public function getCommonFilters(): ICommonFilter
    {
        return new SearchCommonFilter($this->specDetails);
    }
}