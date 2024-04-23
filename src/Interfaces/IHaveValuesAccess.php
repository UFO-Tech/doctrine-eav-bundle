<?php

namespace Ufo\EAV\Interfaces;

use Doctrine\Common\Collections\Collection;
use Ufo\EAV\Entity\Value;

interface IHaveValuesAccess
{
    /**
     * @return Collection|Value[]
     */
    public function getValues(): Collection|array;

    public function setValue(Value $value): static;

    public function removeValue(Value $value): static;
}