<?php

namespace Ufo\EAV\Filters;

use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Entity\Views\SpecDetailsJson;
use Ufo\EAV\Filters\Abstraction\AbstractCommonFilter;

use function array_sum;
use function count;
use function explode;
use function Symfony\Component\Translation\t;

class SearchCommonFilter extends AbstractCommonFilter
{
    /**
     * @var SpecDetailsJson[]
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
        $count = 0;
        foreach ($specsDetails as $specDetails) {
            if ($specDetails instanceof SpecDetailsJson) {
                $count++;
                $this->addSpecDetail($specDetails);
            }
        }
        foreach ($this->params as $tag => $specDetail) {
            $counts = array_sum($this->counts[$tag]);
            if ($counts < $count) {
                unset($this->params[$tag]);
                unset($this->values[$tag]);
                unset($this->counts[$tag]);
            }
        }
    }

    public function addSpecDetail(SpecDetailsJson $specDetails): self
    {
        $this->specsDetails[] = $specDetails;

        $this->addSpec($specDetails->getSpec());

        foreach ($specDetails->getSpecValues() as $specValues) {
            if (!$specValues['filter']) continue;
            $this->addParam($specValues['tag'], $specValues['name'])->addValue($specValues['tag'], $specValues['value']);
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