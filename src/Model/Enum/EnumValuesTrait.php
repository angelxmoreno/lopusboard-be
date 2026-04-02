<?php
declare(strict_types=1);

namespace App\Model\Enum;

use BackedEnum;

/**
 * Shared helpers for backed enums used in model validation.
 */
trait EnumValuesTrait
{
    /**
     * Returns the backed values for all enum cases.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn(BackedEnum $case): string => $case->value,
            self::cases(),
        );
    }
}
