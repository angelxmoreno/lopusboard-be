<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum AttachmentLinkableType: string
{
    use EnumValuesTrait;

    case Issue = 'issue';
    case WikiPage = 'wiki_page';
}
