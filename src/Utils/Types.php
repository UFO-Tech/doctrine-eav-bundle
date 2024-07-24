<?php

namespace Ufo\EAV\Utils;

use function str_contains;

enum Types: string
{
    case BOOLEAN = 'boolean';
    case FILE = 'file';
    case NUMBER = 'number';
    case OPTIONS = 'options';
    case STRING = 'string';

    public static function castType(Types $type, string|int|bool $value): string|int|bool|float
    {
        return match ($type) {
            self::BOOLEAN => (bool)$value,
            self::NUMBER => !str_contains($value, '.') ? (int)$value : (float)$value,
            self::STRING => (string)$value,
            default => $value,
        };
    }
}