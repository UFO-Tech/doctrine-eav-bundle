<?php

namespace Ufo\EAV\Services;

use Doctrine\ORM\EntityManagerInterface;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Fillers\StrategistFiller;
use Ufo\EAV\Filters\Abstraction\ICommonFilter;
use Ufo\EAV\Filters\FilterRow\FilterData;

class FilterService
{
    const int DEFAULT_LIMIT = 20;

    /**
     * @var SpecDetail[]
     */
    protected array $specDetails = [];

    protected FilterData $filterData;

    public function __construct(
        protected StrategistFiller $filler,
        protected EntityManagerInterface $em
    )
    {
        $this->filterData = new FilterData();
    }

    /**
     * @return Spec[]
     */
    public function getSpecs(int $page, int $limit = self::DEFAULT_LIMIT): array
    {
        $this->filler->filterResult($this->filterData);
        $offset = ($page - 1) * $limit;
        return $this->filler->getSpecs($limit, $offset);
    }

    public function getCommonFilters(?string $skipEnv = null): ICommonFilter
    {
        return $this->filler->getCommonFilters($skipEnv);
    }

    public function applyFilterData(FilterData $filterData): static
    {
        $this->filterData = $filterData;
        return $this;
    }

    public function getFillerStrategist(): StrategistFiller
    {
        return $this->filler;
    }

}