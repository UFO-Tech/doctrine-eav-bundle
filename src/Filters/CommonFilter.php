<?php

namespace Ufo\EAV\Filters;

use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Filters\Abstraction\AbstractCommonFilter;
use Ufo\EAV\Utils\Types;

use function count;
use function explode;

class CommonFilter extends AbstractCommonFilter
{
    /**
     * @var SpecDetail[]
     */
    protected array $specsDetails = [];

    protected array $specs = [];

    public function __construct(array $specsDetails = [])
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
        $this->specsDetails[] = $specDetails;
        $this->addSpec($specDetails->getSpec())
            ->addParam($specDetails->paramTag, $specDetails->paramName)
        ;
        if ($specDetails->valueType === 'options') {
            $values = explode(',', $specDetails->value);
            foreach ($values as $value) {
                $this->addValue($specDetails->paramTag, $value);
            }
        } else {
            $value = Types::castType(Types::from($specDetails->valueType), $specDetails->value);
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