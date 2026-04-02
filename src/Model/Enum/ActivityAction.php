<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum ActivityAction: string
{
    use EnumValuesTrait;

    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case StatusChanged = 'status_changed';
    case Assigned = 'assigned';
    case Unassigned = 'unassigned';
    case PriorityChanged = 'priority_changed';
    case RelationAdded = 'relation_added';
    case RelationRemoved = 'relation_removed';
    case CommentAdded = 'comment_added';
    case FileAttached = 'file_attached';
    case Moved = 'moved';
}
