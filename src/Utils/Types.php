<?php

namespace Ufo\EAV\Utils;

enum Types: string
{
    case BOOLEAN = 'boolean';
    case FILE = 'file';
    case NUMBER = 'number';
    case OPTIONS = 'options';
    case STRING = 'string';

}