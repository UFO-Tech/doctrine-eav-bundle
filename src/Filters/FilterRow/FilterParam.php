<?php

namespace Ufo\EAV\Filters\FilterRow;

use function array_keys;
use function implode;

class FilterParam
{
    /**
     * @var FilterValue[]
     */
    protected array $values;

    public function __construct(readonly public string $tag) {}

    public function getValues(): array
    {
        return $this->values;
    }

    public function addValue(FilterValue $value): static
    {
        $this->values[$value->content] = $value;
        return $this;
    }

    public function getValuesArray(): array
    {
        return array_keys($this->values);
    }
}