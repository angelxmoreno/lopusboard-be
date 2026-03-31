<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Project Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $created_by
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\ActivityLog[] $activity_log
 * @property \App\Model\Entity\Attachment[] $attachments
 * @property \App\Model\Entity\Department[] $departments
 * @property \App\Model\Entity\Issue[] $issues
 * @property \App\Model\Entity\ProjectMember[] $project_members
 * @property \App\Model\Entity\Status[] $statuses
 * @property \App\Model\Entity\WikiPage[] $wiki_pages
 */
class Project extends Entity
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
        'name' => true,
        'slug' => true,
        'description' => true,
        'created_by' => true,
        'created' => true,
        'modified' => true,
        'activity_log' => true,
        'attachments' => true,
        'departments' => true,
        'issues' => true,
        'project_members' => true,
        'statuses' => true,
        'wiki_pages' => true,
    ];
}
