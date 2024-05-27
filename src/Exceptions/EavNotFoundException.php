<?php

namespace Ufo\EAV\Exceptions;

use Doctrine\ORM\EntityNotFoundException;

class EavNotFoundException extends EntityNotFoundException implements \Throwable
{

}