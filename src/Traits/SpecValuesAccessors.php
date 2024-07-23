<?php

namespace Ufo\EAV\Traits;

use Doctrine\Common\Collections\Collection;
use Throwable;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Value;
use Ufo\EAV\EventsSubscribers\RemoveSubscriber;

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
        } catch (Throwable) {
            $values = $this->state->getValues();
        }
        return $values;
    }

    public function getValue(string $paramTag)
    {
        $value = null;
        $this->getValues()->filter(function (Value $val) use ($paramTag, &$value) {
            if ($val->getParam()->getTag() === $paramTag) {
                $value = $val->getContent();
            }
        });
        return $value;
    }

    public function setValue(Value $value, bool $replace = true): static
    {
        $values = $this->getValues();
        $values->filter(function (Value $val) use ($value, $replace) {
            if ($val->getParam() === $value->getParam()) {
                if ($replace) {
                    $this->removeValue($val);
                    RemoveSubscriber::add($val);
                } else {
                    $child = $this->state->addChildren();
                    $child->getValues()->add($val);
                    $child->addParam($val->getParam());
                }
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
        } catch (Throwable) {
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