<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WikiPageLink Entity
 *
 * @property int $id
 * @property int $source_page_id
 * @property int $target_page_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\SourcePage $source_page
 * @property \App\Model\Entity\TargetPage $target_page
 */
class WikiPageLink extends Entity
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
        'source_page_id' => true,
        'target_page_id' => true,
        'created' => true,
        'modified' => true,
        'source_page' => true,
        'target_page' => true,
    ];
}
