<?php

namespace Ufo\EAV\Fillers;

use AllowDynamicProperties;
use Doctrine\ORM\EntityManagerInterface;
use Ufo\EAV\Fillers\Interfaces\IFiller;
use Ufo\EAV\Filters\Abstraction\ICommonFilter;
use Ufo\EAV\Filters\FilterRow\FilterData;

use function get_class;

#[AllowDynamicProperties]
class StrategistFiller extends AbstractFiller
{
    protected IFiller $filler;

    public function __construct(
        protected EntityManagerInterface $em,
        protected IFiller $allSpecsFiller,
        protected IFiller $filteredSpecsFiller,
    )
    {
        $this->changeFiller($this->allSpecsFiller);
        parent::__construct($em);
    }

    /**
     * @param IFiller $filler
     * @return static
     */
    public function changeFiller(IFiller $filler): static
    {
        $this->filler = $filler;
        return $this;
    }

    public function filterResult(FilterData $filterData): static
    {
        $baseFiller = get_class($this->allSpecsFiller);
        $baseFiller2 = get_class($this->filteredSpecsFiller);
        if ($this->filler instanceof $baseFiller || $this->filler instanceof $baseFiller2) {
            ($filterData->isEmpty())
                ? $this->changeFiller($this->allSpecsFiller)
                : $this->changeFiller($this->filteredSpecsFiller)
            ;
        }

        $this->filler->filterResult($filterData);
        return $this;
    }

    public function getSpecIds(): array
    {
        return $this->filler->getSpecIds();
    }

    public function getSpecDetails(): array
    {
        return $this->filler->getSpecDetails();
    }

    public function getSpecs(int $limit, int $offset): array
    {
        return $this->filler->getSpecs($limit, $offset);
    }

    public function getCommonFilters(?string $env = null): ICommonFilter
    {
        return $this->filler->getCommonFilters($env);
    }
}