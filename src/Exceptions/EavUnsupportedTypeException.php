<?php

namespace Ufo\EAV\Exceptions;

class EavUnsupportedTypeException extends \InvalidArgumentException
{
    protected $message = 'Unsupported type entity for map';
}