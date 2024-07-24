<?php

namespace Ufo\EAV\Filters\FilterRow;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Value;
use Ufo\EAV\Exceptions\EavNotFoundException;

use function asort;
use function count;

class FilterData
{
    /**
     * @var FilterParam[]
     */
    protected array $params = [];

    public function getParams(): array
    {
        return $this->params;
    }

    public function getParam(string $tag): FilterParam
    {
        return $this->params[$tag] ?? throw new EavNotFoundException("Param '$tag' not found");
    }

    public function addParam(FilterParam $param): static
    {
        $this->params[$param->tag] = $param;
        return $this;
    }

    public function isEmpty(): bool
    {
        return count($this->params) === 0;
    }

    public function exclude(string $tag): static
    {
        unset($this->params[$tag]);
        return $this;
    }
}