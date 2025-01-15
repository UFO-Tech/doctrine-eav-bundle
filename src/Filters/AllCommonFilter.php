<?php

namespace Ufo\EAV\Filters;

use Ufo\EAV\Entity\Views\CommonParamsFilter;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Filters\Abstraction\AbstractCommonFilter;
use Ufo\EAV\Utils\Types;

use function asort;
use function count;
use function explode;
use function is_array;
use function trim;

class AllCommonFilter extends AbstractCommonFilter
{
    /**
     * @var CommonParamsFilter[]
     */
    protected array $commonParams = [];

    public function __construct(
        array $commonParams,
        protected int $count
    )
    {
        $this->setCommonParams($commonParams);
    }

    /**
     * @param CommonParamsFilter[] $commonParams
     * @return void
     */
    public function setCommonParams(array $commonParams): void
    {
        foreach ($commonParams as $commonParam) {
            if ($commonParam instanceof CommonParamsFilter) {
                $this->addCommonParam($commonParam);
            }
        }
    }

    public function addCommonParam(CommonParamsFilter $commonParam): self
    {
        $this->commonParams[] = $commonParam;
        $this->addParam($commonParam->paramTag, $commonParam->paramName);

        $value = Types::from($commonParam->valueType)->castType($commonParam->value);
        if (is_array($value)) {
            foreach ($value as $val) {
                $this->addValue($commonParam->paramTag, $val, $commonParam->specCount);
            }
        } else {
            $this->addValue($commonParam->paramTag, $value, $commonParam->specCount);
        }
        return $this;
    }

    public function addValue(string $paramTag, string|float|bool|int $value, int $count = 0): self
    {
        $this->values[$paramTag][$value] = $value;
        $this->counts[$paramTag][$value] = $count;
        return $this;
    }

    public function countSpecs(): int
    {
        return $this->count;
    }

}