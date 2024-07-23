<?php

namespace Ufo\EAV\Services;

use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Param;
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

    public function getOption(Param $param, string $value): Option
    {
        try {
            $option = $this->optionRepo->get($param, $value);
        } catch (EavNotFoundException) {
            $option = new Option($param, $value);
            $this->em->persist($option);
        }
        return $option;
    }

    public function valueForParam(string $param, mixed $value): Value
    {
        return Value::create($this->getParam($param), $value);
    }

    public function optionForParam(string $param, string $value): Option
    {
        return $this->getOption($this->getParam($param), $value);
    }


}