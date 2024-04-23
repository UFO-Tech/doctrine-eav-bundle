<?php

namespace Ufo\EAV\Interfaces;

use Ufo\EAV\Entity\Spec;

interface IHaveSpecAccess
{

    public function getSpec(): Spec;
}