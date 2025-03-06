<?php

namespace Ufo\EAV\Services;

use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Value;
use Ufo\EAV\Exceptions\EavNotFoundException;
use Ufo\EAV\Traits\EavRepositoryAccess;

class EavFactoryService
{
    use EavRepositoryAccess;

    public function getParam(
        string $tag,
        ?string $name = null,
        bool $filtered = true,
        array $jsonSchema = []
    ): Param
    {
        try {
            $param = $this->paramRepo->get($tag);
        } catch (EavNotFoundException) {
            $param = new Param($tag, $name, $filtered, $jsonSchema);
            $this->em->persist($param);
        }
        return $param;
    }

    public function getOption(
        Param $param,
        string $value,
        ?string $locale = null,
        mixed $baseValue = null,
    ): Option
    {
        try {
            $option = $this->optionRepo->get($param, $value, $locale);
        } catch (EavNotFoundException) {
            $baseOption = null;
            if ($locale) {
                try {
                    $baseOption = $this->optionRepo->get($param, $baseValue);
                } catch (EavNotFoundException) {}
            }
            $option = new Option($param, $value, $locale, $baseOption);
            $this->em->persist($option);
        }
        return $option;
    }

    public function valueForParam(
        Spec $spec,
        string $param,
        mixed $value,
        ?string $locale = null,
    ): Value
    {
        $param = $this->getParam($param);
        $baseValue = null;
        if ($locale) {
            $baseValue = $this->valueRepo->get($spec, $param);
        }
        $value = Value::create($param, $value, $locale, $baseValue);
        $this->em->persist($value);
        $this->em->flush();
        return $value;
    }

    public function optionForParam(
        string $param,
        string $value,
        ?string $locale = null,
        mixed $baseValue = null,
    ): Option
    {
        return $this->getOption($this->getParam($param), $value, $locale, $baseValue);
    }


}