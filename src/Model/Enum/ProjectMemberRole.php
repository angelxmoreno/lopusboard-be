<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum ProjectMemberRole: string
{
    use EnumValuesTrait;

    case Admin = 'admin';
    case Member = 'member';
}
