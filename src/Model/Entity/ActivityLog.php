<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ActivityLog Entity
 *
 * @property int $id
 * @property int $project_id
 * @property int $actor_id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $action
 * @property array|null $old_value
 * @property array|null $new_value
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Actor $actor
 */
class ActivityLog extends Entity
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
    protected array $_accessible = [];
}
