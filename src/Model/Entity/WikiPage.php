<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WikiPage Entity
 *
 * @property int $id
 * @property int $project_id
 * @property int|null $parent_id
 * @property string $title
 * @property string $slug
 * @property string|null $body
 * @property string $position
 * @property int $created_by
 * @property int|null $last_edited_by
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\ParentWikiPage $parent_wiki_page
 * @property \App\Model\Entity\WikiPageRevision[] $wiki_page_revisions
 * @property \App\Model\Entity\ChildWikiPage[] $child_wiki_pages
 */
class WikiPage extends Entity
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
        'project_id' => true,
        'parent_id' => true,
        'title' => true,
        'slug' => true,
        'body' => true,
        'position' => true,
        'created_by' => true,
        'last_edited_by' => true,
        'created' => true,
        'modified' => true,
        'project' => true,
        'parent_wiki_page' => true,
        'wiki_page_revisions' => true,
        'child_wiki_pages' => true,
    ];
}
