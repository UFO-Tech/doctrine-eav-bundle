<?php

namespace Ufo\EAV\Traits;

use Doctrine\Common\Collections\Collection;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Value;
use Ufo\EAV\EventsListeners\RemoveListener;

trait SpecValuesAccessors
{

    protected Spec $state;

    /**
     * @return Collection|Value[]
     */
    public function getValues(): Collection|array
    {
        try {
            $values = $this->values;
        } catch (\Throwable) {
            $values = $this->state->getValues();
        }
        return $values;
    }

    public function setValue(Value $value): static
    {
        $values = $this->getValues();
        $values->filter(function (Value $val) use ($value) {
            if ($val->getParam() === $value->getParam()) {
                $this->removeValue($val);
                RemoveListener::add($val);
            }
        });
        $values->add($value);
        $this->addParam($value->getParam());
        return $this;
    }

    public function removeValue(Value $value): static
    {
        $this->getValues()->removeElement($value);
        return $this;
    }

    /**
     * @return Collection|Param[]
     */
    public function getParams(): Collection|array
    {
        try {
            $params = $this->params;
        } catch (\Throwable) {
            $params = $this->state->getParams();
        }
        return $params;
    }

    public function addParam(Param $param): static
    {
        $params = $this->getParams();
        $needAdd = true;
        $params->filter(function (Param $p) use ($param, &$needAdd) {
            if ($p->getTag() === $param->getTag()) {
                $needAdd = false;
            }
        });
        if ($needAdd) {
            $params->add($param);
        }
        return $this;
    }

    public function removeParam(Param $value): static
    {
        $this->getParams()->removeElement($value);
        return $this;
    }
}