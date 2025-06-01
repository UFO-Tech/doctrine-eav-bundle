<?php

namespace Ufo\EAV\Filters\Abstraction;

use function array_keys;
use function is_string;
use function ksort;
use function trim;

abstract class AbstractCommonFilter implements ICommonFilter
{
    protected array $params = [];

    protected array $values = [];

    protected array $counts = [];

    public function getParams(): array
    {
        return $this->params;
    }

    public function getParamsTags(): array
    {
        return array_keys($this->params);
    }

    public function getValues(string $paramTag): array
    {
        ksort($this->values[$paramTag]);
        return $this->values[$paramTag];
    }

    public function getValuesIds(string $paramTag): array
    {
        return array_keys($this->values[$paramTag]);
    }

    public function getCounts(string $paramTag, string|int|float|bool $value): int
    {
        return $this->counts[$paramTag][$value] ?? 0;
    }

    public function addValue(string $paramTag, string|int|float|bool $value): self
    {
        $value = is_string($value) ? trim($value) : $value;
        $this->values[$paramTag][$value] = $value;
        if (!isset($this->counts[$paramTag][$value])) {
            $this->counts[$paramTag][$value] = 0;
        }
        $this->counts[$paramTag][$value]++;
        return $this;
    }

    public function addParam(string $paramTag, string $paramName): self
    {
        $this->params[$paramTag] = $paramName;
        if (!isset($this->values[$paramTag])) {
            $this->values[$paramTag] = [];
        }
        return $this;
    }

    protected function getContextFilter(): callable
    {
        return fn(array $context) => (empty($context) || ($context[$this->env] ?? false));
    }
}