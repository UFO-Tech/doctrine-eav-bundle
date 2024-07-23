<?php

namespace Ufo\EAV\Services;

use Doctrine\ORM\EntityManagerInterface;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Fillers\StrategistFiller;
use Ufo\EAV\Filters\Abstraction\ICommonFilter;
use Ufo\EAV\Filters\FilterRow\FilterData;
use Ufo\EAV\Filters\CommonFilter;

class FilterService
{
    const DEFAULT_LIMIT = 20;

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


//    protected function fillFilter(Filter $filter, bool $all = false): void
//    {
//        $params = $filter->getParams();
//        foreach ($params as $param) {
//            $values = $param->getValues();
//            $filter->setValues($param,
//               $values->filter(function (Value $v) use ($filter, $all) {
//
//                   $res = $v->getSpecs()->filter(function (Spec $spec) use ($filter) {
//                       return in_array($spec->getId(), $filter->getSpecsIds());
//                   });
//                   return $all || $res->count() > 0;
//
//               })->toArray()
//            );
//        }
//    }

    /**
     * @return Spec[]
     */
    public function getSpecs(int $page, int $limit = self::DEFAULT_LIMIT): array
    {
        $this->filler->filterResult($this->filterData);
        $offset = ($page - 1) * $limit;
        return $this->filler->getSpecs($limit, $offset);
    }

    public function getCommonFilters(): ICommonFilter
    {
        return $this->filler->getCommonFilters();
    }

    public function applyFilterData(FilterData $filterData): static
    {
        $this->filterData = $filterData;
        return $this;
    }

}