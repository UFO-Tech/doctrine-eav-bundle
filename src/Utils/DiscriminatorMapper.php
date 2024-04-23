<?php

namespace Ufo\EAV\Utils;

use Ufo\EAV\Entity\Option;
use Ufo\EAV\Exceptions\EavUnsupportedTypeException;

class DiscriminatorMapper
{
    /**
     * Determines the appropriate class for the provided value using the ValueEntityMap enum.
     *
     * @param mixed $value The value based on which to determine the class.
     * @return string The class name associated with the value type.
     */
    public static function valueClass(mixed $value): string
    {
        return match(true) {
            is_bool($value) => ValueEntityMap::BOOLEAN->value,
            is_int($value) || is_float($value) => ValueEntityMap::NUMBER->value,
            is_string($value) => ValueEntityMap::STRING->value,
            is_array($value) && isset($value['path']) && isset($value['mime']) => ValueEntityMap::FILE->value,
            is_array($value) && static::arrayHasOption($value) => ValueEntityMap::OPTIONS->value,
            default => throw new EavUnsupportedTypeException()
        };
    }
    private static function arrayHasOption(array $array): bool {
        return !empty(array_filter($array, function ($item) {
            return $item instanceof Option;
        }));
    }

}