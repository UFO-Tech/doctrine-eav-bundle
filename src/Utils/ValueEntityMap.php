<?php

namespace Ufo\EAV\Utils;

use Ufo\EAV\Entity\Discriminators\Values\ValueBoolean;
use Ufo\EAV\Entity\Discriminators\Values\ValueFile;
use Ufo\EAV\Entity\Discriminators\Values\ValueNumber;
use Ufo\EAV\Entity\Discriminators\Values\ValueOption;
use Ufo\EAV\Entity\Discriminators\Values\ValueString;

enum ValueEntityMap: string
{
    case BOOLEAN = ValueBoolean::class;
    case FILE = ValueFile::class;
    case NUMBER = ValueNumber::class;
    case OPTIONS = ValueOption::class;
    case STRING = ValueString::class;

}