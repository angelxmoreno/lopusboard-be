<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

/**
 * Synchronizes internal wiki links after page saves.
 */
class WikiLinkSyncBehavior extends Behavior
{
    /**
     * Parses internal links and syncs link records.
     *
     * @param \Cake\Event\EventInterface $event The ORM event instance.
     * @param \Cake\Datasource\EntityInterface $entity The saved wiki page.
     * @param \ArrayObject<string, mixed> $options Save options.
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $sourcePageId = (int)$entity->get('id');
        $projectId = (int)$entity->get('project_id');
        if ($sourcePageId === 0 || $projectId === 0) {
            return;
        }

        $titles = $this->extractLinkedTitles((string)($entity->get('body') ?? ''));
        $linksTable = $this->table()->getAssociation('WikiPageLinks')->getTarget();
        $linksTable->deleteAll(['source_page_id' => $sourcePageId]);

        if ($titles === []) {
            return;
        }

        $targetPages = $this->table()->find()
            ->select(['id'])
            ->where([
                'WikiPages.project_id' => $projectId,
                'WikiPages.title IN' => $titles,
            ])
            ->disableHydration()
            ->all()
            ->extract('id')
            ->toList();

        if ($targetPages === []) {
            return;
        }

        $linkEntities = $linksTable->newEntities(
            array_map(
                static fn(int $targetPageId): array => [
                    'source_page_id' => $sourcePageId,
                    'target_page_id' => $targetPageId,
                ],
                array_values(array_unique(array_map('intval', $targetPages))),
            ),
            ['accessibleFields' => ['source_page_id' => true, 'target_page_id' => true]],
        );

        $linksTable->saveManyOrFail($linkEntities);
    }

    /**
     * Extracts unique internal wiki link titles from markdown content.
     *
     * @param string $body The wiki body content.
     * @return array<int, string>
     */
    public function extractLinkedTitles(string $body): array
    {
        preg_match_all('/\[\[([^\]]+)\]\]/', $body, $matches);
        $titles = array_map(
            static fn(string $title): string => trim($title),
            $matches[1] ?? [],
        );
        $titles = array_filter($titles, static fn(string $title): bool => $title !== '');

        return array_values(array_unique($titles));
    }
}
