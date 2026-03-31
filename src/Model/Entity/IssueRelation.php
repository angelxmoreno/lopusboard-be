<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * IssueRelation Entity
 *
 * @property int $id
 * @property int $issue_id
 * @property int $related_issue_id
 * @property string $type
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Issue $issue
 * @property \App\Model\Entity\RelatedIssue $related_issue
 */
class IssueRelation extends Entity
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
        'related_issue_id' => true,
        'type' => true,
    ];
}
