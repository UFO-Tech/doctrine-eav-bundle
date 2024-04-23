<?php

namespace Ufo\EAV\VO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Value;

class Filter
{
    /**
     * @var Spec[]
     */
    protected array $specs = [];

    /**
     * @var Param[]
     */
    protected array $params = [];
    /**
     * @var array
     */
    protected array $values = [];

    public function __construct(array $specs = [])
    {
        $this->setSpecs($specs);
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

    /**
     * @param Spec[] $specs
     * @return void
     */
    public function setSpecs(array $specs): void
    {
        foreach ($specs as $spec) {
            if ($spec instanceof Spec) {
                $this->addSpec($spec);
            }
        }
    }

    public function addSpec(Spec $spec): self
    {
        $this->specs[$spec->getId()] = $spec->getId();
        return $this;
    }

    /**
     * @return Param[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getParamsTags(): array
    {
        return array_keys($this->params);
    }

    /**
     * @param Param[] $params
     * @return void
     */
    public function setParams(array $params): void
    {
        foreach ($params as $param) {
            if ($param instanceof Param) {
                $this->addParam($param);
            }
        }
    }

    public function addParam(Param $param): self
    {
        $this->params[$param->getTag()] = $param;
        if (!isset($this->values[$param->getTag()])) {
            $this->values[$param->getTag()] = [];
        }

        return $this;
    }

    /**
     * @return Value[]
     */
    public function getValues(Param $param): array
    {
        return $this->values[$param->getTag()];
    }

    public function getValuesIds(Param $param): array
    {
        return array_keys($this->values[$param->getTag()]);
    }

    /**
     * @param Param $param
     * @param Value[] $values
     * @return void
     */
    public function setValues(Param $param, array $values): void
    {
        foreach ($values as $value) {
            if ($value instanceof Value) {
                $this->addValue($param, $value);
            }
        }
    }

    public function addValue(Param $param, Value $value): self
    {
        $this->values[$param->getTag()][$value->getId()] = $value;
        return $this;
    }

}