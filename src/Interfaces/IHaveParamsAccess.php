<?php

namespace Ufo\EAV\Interfaces;

use Doctrine\Common\Collections\Collection;
use Ufo\EAV\Entity\Param;

interface IHaveParamsAccess
{
    /**
     * @return Collection|Param[]
     */
    public function getParams(): Collection|array;

    public function addParam(Param $value): static;

    public function removeParam(Param $value): static;
}