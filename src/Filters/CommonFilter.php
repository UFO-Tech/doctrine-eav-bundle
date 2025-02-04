<?php

namespace Ufo\EAV\Filters;

use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Filters\Abstraction\AbstractCommonFilter;
use Ufo\EAV\Utils\Types;

use function count;
use function explode;
use function is_array;

class CommonFilter extends AbstractCommonFilter
{
    /**
     * @var SpecDetail[]
     */
    protected array $specsDetails = [];

    protected array $specs = [];

    public function __construct(
        array $specsDetails = [],
        protected ?string $skipEnv = null
    )
    {
        $this->setSpecsDetails($specsDetails);
    }

    /**
     * @param SpecDetail[] $specsDetails
     * @return void
     */
    public function setSpecsDetails(array $specsDetails): void
    {
        foreach ($specsDetails as $specDetails) {
            if ($specDetails instanceof SpecDetail) {
                $this->addSpecDetail($specDetails);
            }
        }
    }

    public function addSpecDetail(SpecDetail $specDetails): self
    {
        if (!$this->getContextFilter()($specDetails->context)) return $this;
        $this->specsDetails[] = $specDetails;
        $this->addSpec($specDetails->getSpec())
            ->addParam($specDetails->paramTag, $specDetails->paramName)
        ;
        $value = Types::from($specDetails->valueType)->castType($specDetails->value);

        if (is_array($value)) {
            foreach ($value as $val) {
                $this->addValue($specDetails->paramTag, $val);
            }
        } else {
            $this->addValue($specDetails->paramTag, $value);
        }
        return $this;
    }

    public function addSpec(Spec $spec): self
    {
        $this->specs[$spec->getId()] = $spec;
        return $this;
    }

    /**
     * @return Spec[]
     */
    public function getSpecs(): array
    {
        return $this->specs;
    }

    public function getSpecsIds(): array
    {
        return array_keys($this->specs);
    }

    public function countSpecs(): int
    {
        return count($this->specs);
    }
}