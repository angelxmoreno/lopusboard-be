<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Issue Entity
 *
 * @property int $id
 * @property int $project_id
 * @property int|null $parent_id
 * @property string $type
 * @property string $title
 * @property string|null $description
 * @property int $status_id
 * @property string $priority
 * @property int|null $assignee_id
 * @property int|null $department_id
 * @property \Cake\I18n\Date|null $due_date
 * @property string $position
 * @property int $created_by
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\ParentIssue $parent_issue
 * @property \App\Model\Entity\Status $status
 * @property \App\Model\Entity\Assignee $assignee
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\IssueRelation[] $issue_relations
 * @property \App\Model\Entity\ChildIssue[] $child_issues
 */
class Issue extends Entity
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
        'parent_id' => true,
        'type' => true,
        'title' => true,
        'description' => true,
        'status_id' => true,
        'priority' => true,
        'assignee_id' => true,
        'department_id' => true,
        'due_date' => true,
        'position' => true,
    ];
}
