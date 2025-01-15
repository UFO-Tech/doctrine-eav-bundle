<?php

namespace Ufo\EAV\Services;

use Ufo\EAV\Filters\FilterRow\FilterData;
use Ufo\EAV\Filters\FilterRow\FilterParam;
use Ufo\EAV\Filters\FilterRow\FilterValue;

use function explode;
use function is_null;
use function is_string;
use function trim;

class FilterDataFactory
{
    public function fromArray(array $data): FilterData
    {
        // ['param' => ['value1', 'value2']]
        $filterData = new FilterData();
        foreach ($data as $paramTag => $values) {
            $filterData->addParam(new FilterParam(trim($paramTag)));
            foreach ($values as $value) {
                if (is_string($value)) {
                    $value = trim($value);
                }
                $filterData->getParam($paramTag)->addValue(new FilterValue($value));
            }
        }
        return $filterData;
    }

    public function fromFilterRow(?string $filterRow = null): FilterData
    {
        $result = [];
        if (!is_null($filterRow)) {
            // param1=value1,value2;param2=value3,value4
            $paramPairs = explode(';', $filterRow);

            foreach ($paramPairs as $pair) {
                [$key, $values] = explode('=', $pair);
                $result[$key] = explode(',', $values);
            }
        }

        return $this->fromArray($result);
    }

}