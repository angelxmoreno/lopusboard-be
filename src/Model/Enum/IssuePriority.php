<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum IssuePriority: string
{
    use EnumValuesTrait;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';
}
