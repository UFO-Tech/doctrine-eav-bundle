<?php

namespace Ufo\EAV\Fillers\Interfaces;

use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Filters\Abstraction\ICommonFilter;
use Ufo\EAV\Filters\FilterRow\FilterData;

interface IFiller
{
    /**
     * @param FilterData $filterData
     * @return static
     */
    public function filterResult(FilterData $filterData): static;

    /**
     * @param int $limit
     * @param int $offset
     * @return Spec[]
     */
    public function getSpecs(int $limit, int $offset): array;

    /**
     * @return SpecDetail[]
     */
    public function getSpecDetails(): array;

    public function getSpecIds(): array;

    public function getCommonFilters(?string $skipEnv = null): ICommonFilter;
}