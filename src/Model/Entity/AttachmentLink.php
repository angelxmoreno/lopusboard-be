<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * AttachmentLink Entity
 *
 * @property int $id
 * @property int $attachment_id
 * @property string $linkable_type
 * @property int $linkable_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Attachment $attachment
 */
class AttachmentLink extends AppEntity
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
        'linkable_type' => true,
        'linkable_id' => true,
    ];
}
