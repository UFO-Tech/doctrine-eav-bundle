<?php

declare(strict_types=1);

namespace Ufo\EAV\DBAL;

use BackedEnum;
use Ufo\DoctrineHelper\DBAL\AbstractEnumType;
use Ufo\EAV\Utils\DiscriminatorType;

class DiscriminatorTypeEnumType extends AbstractEnumType
{
    protected string|BackedEnum $enum = DiscriminatorType::class;
}