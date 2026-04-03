<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

/**
 * Persists wiki page revisions after each save.
 */
class WikiRevisionBehavior extends Behavior
{
    /**
     * Creates a new revision row after a wiki page is saved.
     *
     * @param \Cake\Event\EventInterface $event The ORM event instance.
     * @param \Cake\Datasource\EntityInterface $entity The saved wiki page.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $wikiPageId = (int)$entity->get('id');
        $editedBy = (int)($entity->get('last_edited_by') ?? $entity->get('created_by') ?? 0);
        if ($wikiPageId === 0 || $editedBy === 0) {
            return;
        }

        $revisionsTable = $this->table()->getAssociation('WikiPageRevisions')->getTarget();
        $revisionNumber = $this->nextRevisionNumber($wikiPageId);
        $revision = $revisionsTable->newEntity(
            [
                'wiki_page_id' => $wikiPageId,
                'body' => (string)($entity->get('body') ?? ''),
                'revision_number' => $revisionNumber,
                'edited_by' => $editedBy,
            ],
            [
                'accessibleFields' => [
                    'wiki_page_id' => true,
                    'body' => true,
                    'revision_number' => true,
                    'edited_by' => true,
                ],
            ],
        );

        $revisionsTable->saveOrFail($revision);
    }

    /**
     * Returns the next revision number for a wiki page.
     *
     * @param int $wikiPageId The wiki page identifier.
     * @return int
     */
    public function nextRevisionNumber(int $wikiPageId): int
    {
        $revisionsTable = $this->table()->getAssociation('WikiPageRevisions')->getTarget();
        $result = $revisionsTable->find()
            ->select(['max_revision' => $revisionsTable->find()->func()->max('revision_number')])
            ->where(['wiki_page_id' => $wikiPageId])
            ->disableHydration()
            ->first();

        return (int)($result['max_revision'] ?? 0) + 1;
    }
}
