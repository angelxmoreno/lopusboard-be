<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Attachment Entity
 *
 * @property int $id
 * @property int $project_id
 * @property string $storage_provider
 * @property string $filename
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property string|null $storage_path
 * @property string|null $external_url
 * @property string|null $external_id
 * @property int $uploaded_by
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\AttachmentLink[] $attachment_links
 */
class Attachment extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'storage_provider' => true,
        'filename' => true,
        'mime_type' => true,
        'file_size' => true,
        'storage_path' => true,
        'external_url' => true,
        'external_id' => true,
    ];
}
