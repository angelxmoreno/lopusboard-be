<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * Department Entity
 *
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property string|null $color
 * @property string $position
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Issue[] $issues
 */
class Department extends AppEntity
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
        'color' => true,
        'position' => true,
    ];
}
