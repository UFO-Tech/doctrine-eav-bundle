<?php

namespace Ufo\EAV\Filters\Abstraction;

use function array_keys;
use function ksort;

interface ICommonFilter
{
    public function getParams(): array;

    public function getParamsTags(): array;

    public function getValues(string $paramTag): array;

    public function getValuesIds(string $paramTag): array;

    public function getCounts(string $paramTag, string $value): int;

    public function countSpecs(): int;
}