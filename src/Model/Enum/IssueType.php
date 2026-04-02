<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum IssueType: string
{
    use EnumValuesTrait;

    case Issue = 'issue';
    case Task = 'task';
}
