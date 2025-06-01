<?php

namespace Ufo\EAV\Exceptions;

use InvalidArgumentException;

class EavUnsupportedTypeException extends InvalidArgumentException
{
    protected $message = 'Unsupported type entity for map';
}