<?php

namespace Ufo\EAV\Utils;

use Ufo\EAV\Entity\Discriminators\Values\ValueBoolean;
use Ufo\EAV\Entity\Discriminators\Values\ValueFile;
use Ufo\EAV\Entity\Discriminators\Values\ValueNumber;
use Ufo\EAV\Entity\Discriminators\Values\ValueOption;
use Ufo\EAV\Entity\Discriminators\Values\ValueString;
use Ufo\EAV\Entity\Option;
use Ufo\EAV\Exceptions\EavUnsupportedTypeException;

use function array_filter;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function str_contains;

enum DiscriminatorType: string
{
    case BOOLEAN = 'boolean';
    case FILE = 'file';
    case NUMBER = 'number';
    case OPTIONS = 'options';
    case STRING = 'string';

    public function castType(string|int|bool $value): string|int|bool|float|array|null
    {
        return match ($this) {
            self::BOOLEAN => (bool)$value,
            self::NUMBER => !str_contains($value, '.') ? (int)$value : (float)$value,
            self::STRING => (string)$value,
            self::OPTIONS => explode(', ', $value),
            default => $value,
        };
    }

    /**
     * Determines the appropriate class for the provided value using the ValueEntityMap enum.
     *
     * @param mixed $value The value based on which to determine the class.
     * @return string The class name associated with the value type.
     */
    public static function valueClass(mixed $value): string
    {
        return match(true) {
            is_bool($value) => self::BOOLEAN->entityClass(),
            is_int($value) || is_float($value) => self::NUMBER->entityClass(),
            is_string($value) => self::STRING->entityClass(),
            is_array($value) && isset($value['path']) && isset($value['mime']) => self::FILE->entityClass(),
            is_array($value) && self::arrayHasOption($value) => self::OPTIONS->entityClass(),
            default => throw new EavUnsupportedTypeException()
        };
    }

    private static function arrayHasOption(array $array): bool {
        return !empty(array_filter($array, function ($item) {
            return $item instanceof Option;
        }));
    }

    public function entityClass(): string
    {
        return match ($this) {
            self::BOOLEAN => ValueBoolean::class,
            self::FILE => ValueFile::class,
            self::NUMBER => ValueNumber::class,
            self::OPTIONS => ValueOption::class,
            self::STRING => ValueString::class,
            default => throw new EavUnsupportedTypeException(),
        };
    }
}