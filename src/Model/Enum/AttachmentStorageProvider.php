<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum AttachmentStorageProvider: string
{
    use EnumValuesTrait;

    case GoogleDrive = 'google_drive';
    case Minio = 'minio';
}
