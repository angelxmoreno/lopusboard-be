<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum ActivitySubjectType: string
{
    use EnumValuesTrait;

    case Issue = 'issue';
    case Task = 'task';
    case WikiPage = 'wiki_page';
    case Comment = 'comment';
    case Attachment = 'attachment';
    case Member = 'member';

    /**
     * Returns the subset used for issue-related activity rows.
     *
     * @return list<string>
     */
    public static function issueTaskValues(): array
    {
        return [
            self::Issue->value,
            self::Task->value,
        ];
    }
}
