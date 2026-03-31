<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class InitialTables extends BaseMigration
{
    public bool $autoId = false;

    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     *
     * @return void
     */
    public function up(): void
    {
        $this->table('activity_log')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('actor_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('subject_type', 'enum', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'values' => ['issue', 'task', 'wiki_page', 'comment', 'attachment', 'member'],
            ])
            ->addColumn('subject_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('action', 'enum', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'values' => [
                    'created',
                    'updated',
                    'deleted',
                    'status_changed',
                    'assigned',
                    'unassigned',
                    'priority_changed',
                    'relation_added',
                    'relation_removed',
                    'comment_added',
                    'file_attached',
                    'moved',
                ],
            ])
            ->addColumn('old_value', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('new_value', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'project_id',
                        'subject_type',
                        'subject_id',
                    ])
                    ->setName('idx_activity_subject')
            )
            ->addIndex(
                $this->index([
                        'project_id',
                        'created',
                    ])
                    ->setName('idx_activity_project')
            )
            ->addIndex(
                $this->index('actor_id')
                    ->setName('fk_activity_log_actor_id')
            )
            ->create();

        $this->table('attachment_links')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('attachment_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('linkable_type', 'enum', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'values' => ['issue', 'wiki_page'],
            ])
            ->addColumn('linkable_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('attachment_id')
                    ->setName('fk_attachment_links_attachment_id')
            )
            ->addIndex(
                $this->index(['linkable_type', 'linkable_id'])
                    ->setName('idx_attachment_links_linkable')
            )
            ->create();

        $this->table('attachments')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('storage_provider', 'enum', [
                'default' => 'google_drive',
                'limit' => null,
                'null' => false,
                'values' => ['google_drive', 'minio'],
            ])
            ->addColumn('filename', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('mime_type', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('file_size', 'biginteger', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('storage_path', 'string', [
                'default' => null,
                'limit' => 1000,
                'null' => true,
            ])
            ->addColumn('external_url', 'string', [
                'default' => null,
                'limit' => 1000,
                'null' => true,
            ])
            ->addColumn('external_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('uploaded_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('project_id')
                    ->setName('fk_attachments_project_id')
            )
            ->addIndex(
                $this->index('uploaded_by')
                    ->setName('fk_attachments_uploaded_by')
            )
            ->create();

        $this->table('comments')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('issue_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('body', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('issue_id')
                    ->setName('fk_comments_issue_id')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('fk_comments_user_id')
            )
            ->create();

        $this->table('departments')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('color', 'string', [
                'default' => null,
                'limit' => 7,
                'null' => true,
            ])
            ->addColumn('position', 'decimal', [
                'default' => '0.00000',
                'null' => false,
                'precision' => 10,
                'scale' => 5,
                'signed' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('project_id')
                    ->setName('fk_departments_project_id')
            )
            ->create();

        $this->table('issue_relations')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('issue_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('related_issue_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('type', 'enum', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'values' => ['blocks', 'depends_on', 'duplicates', 'relates_to'],
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'issue_id',
                        'related_issue_id',
                        'type',
                    ])
                    ->setName('uq_relation')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('related_issue_id')
                    ->setName('fk_issue_relations_related_issue_id')
            )
            ->create();

        $this->table('issues')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('type', 'enum', [
                'default' => 'issue',
                'limit' => null,
                'null' => false,
                'values' => ['issue', 'task'],
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => true,
            ])
            ->addColumn('status_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('priority', 'enum', [
                'default' => 'medium',
                'limit' => null,
                'null' => false,
                'values' => ['low', 'medium', 'high', 'critical'],
            ])
            ->addColumn('assignee_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('department_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('due_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('position', 'decimal', [
                'default' => '0.00000',
                'null' => false,
                'precision' => 10,
                'scale' => 5,
                'signed' => true,
            ])
            ->addColumn('created_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('project_id')
                    ->setName('fk_issues_project_id')
            )
            ->addIndex(
                $this->index('parent_id')
                    ->setName('fk_issues_parent_id')
            )
            ->addIndex(
                $this->index('status_id')
                    ->setName('fk_issues_status_id')
            )
            ->addIndex(
                $this->index('assignee_id')
                    ->setName('fk_issues_assignee_id')
            )
            ->addIndex(
                $this->index('department_id')
                    ->setName('fk_issues_department_id')
            )
            ->addIndex(
                $this->index('created_by')
                    ->setName('fk_issues_created_by')
            )
            ->create();

        $this->table('project_members')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('role', 'enum', [
                'default' => 'member',
                'limit' => null,
                'null' => false,
                'values' => ['admin', 'member'],
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'project_id',
                        'user_id',
                    ])
                    ->setName('uq_project_member')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('fk_project_members_user_id')
            )
            ->create();

        $this->table('projects')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('slug')
                    ->setName('slug')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('created_by')
                    ->setName('fk_projects_created_by')
            )
            ->create();

        $this->table('statuses')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('color', 'string', [
                'default' => '#6B7280',
                'limit' => 7,
                'null' => false,
            ])
            ->addColumn('position', 'decimal', [
                'default' => '0.00000',
                'null' => false,
                'precision' => 10,
                'scale' => 5,
                'signed' => true,
            ])
            ->addColumn('is_default', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_done', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('project_id')
                    ->setName('fk_statuses_project_id')
            )
            ->create();

        $this->table('users')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('appwrite_id', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('avatar_url', 'string', [
                'default' => null,
                'limit' => 512,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('appwrite_id')
                    ->setName('appwrite_id')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('email')
                    ->setName('email')
                    ->setType('unique')
            )
            ->create();

        $this->table('wiki_page_links')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('source_page_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('target_page_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'source_page_id',
                        'target_page_id',
                    ])
                    ->setName('uq_wiki_link')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('target_page_id')
                    ->setName('fk_wiki_page_links_target_page_id')
            )
            ->create();

        $this->table('wiki_page_revisions')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('wiki_page_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('body', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => false,
            ])
            ->addColumn('revision_number', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('edited_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('wiki_page_id')
                    ->setName('fk_wiki_page_revisions_page_id')
            )
            ->addIndex(
                $this->index('edited_by')
                    ->setName('fk_wiki_page_revisions_edited_by')
            )
            ->addIndex(
                $this->index(['wiki_page_id', 'revision_number'])
                    ->setName('uq_wiki_page_revision')
                    ->setType('unique')
            )
            ->create();

        $this->table('wiki_pages')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('body', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => true,
            ])
            ->addColumn('position', 'decimal', [
                'default' => '0.00000',
                'null' => false,
                'precision' => 10,
                'scale' => 5,
                'signed' => true,
            ])
            ->addColumn('created_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('last_edited_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'project_id',
                        'slug',
                    ])
                    ->setName('uq_wiki_slug')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('parent_id')
                    ->setName('fk_wiki_pages_parent_id')
            )
            ->addIndex(
                $this->index('created_by')
                    ->setName('fk_wiki_pages_created_by')
            )
            ->addIndex(
                $this->index('last_edited_by')
                    ->setName('fk_wiki_pages_last_edited_by')
            )
            ->create();

        $this->table('activity_log')
            ->addForeignKey(
                $this->foreignKey('actor_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_activity_log_actor_id')
            )
            ->addForeignKey(
                $this->foreignKey('project_id')
                    ->setReferencedTable('projects')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_activity_log_project_id')
            )
            ->update();

        $this->table('attachment_links')
            ->addForeignKey(
                $this->foreignKey('attachment_id')
                    ->setReferencedTable('attachments')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_attachment_links_attachment_id')
            )
            ->update();

        $this->table('attachments')
            ->addForeignKey(
                $this->foreignKey('project_id')
                    ->setReferencedTable('projects')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_attachments_project_id')
            )
            ->addForeignKey(
                $this->foreignKey('uploaded_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_attachments_uploaded_by')
            )
            ->update();

        $this->table('comments')
            ->addForeignKey(
                $this->foreignKey('issue_id')
                    ->setReferencedTable('issues')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_comments_issue_id')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_comments_user_id')
            )
            ->update();

        $this->table('departments')
            ->addForeignKey(
                $this->foreignKey('project_id')
                    ->setReferencedTable('projects')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_departments_project_id')
            )
            ->update();

        $this->table('issue_relations')
            ->addForeignKey(
                $this->foreignKey('issue_id')
                    ->setReferencedTable('issues')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_issue_relations_issue_id')
            )
            ->addForeignKey(
                $this->foreignKey('related_issue_id')
                    ->setReferencedTable('issues')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_issue_relations_related_issue_id')
            )
            ->update();

        $this->table('issues')
            ->addForeignKey(
                $this->foreignKey('assignee_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('SET_NULL')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_issues_assignee_id')
            )
            ->addForeignKey(
                $this->foreignKey('created_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_issues_created_by')
            )
            ->addForeignKey(
                $this->foreignKey('department_id')
                    ->setReferencedTable('departments')
                    ->setReferencedColumns('id')
                    ->setOnDelete('SET_NULL')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_issues_department_id')
            )
            ->addForeignKey(
                $this->foreignKey('parent_id')
                    ->setReferencedTable('issues')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_issues_parent_id')
            )
            ->addForeignKey(
                $this->foreignKey('project_id')
                    ->setReferencedTable('projects')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_issues_project_id')
            )
            ->addForeignKey(
                $this->foreignKey('status_id')
                    ->setReferencedTable('statuses')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_issues_status_id')
            )
            ->update();

        $this->table('project_members')
            ->addForeignKey(
                $this->foreignKey('project_id')
                    ->setReferencedTable('projects')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_project_members_project_id')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_project_members_user_id')
            )
            ->update();

        $this->table('projects')
            ->addForeignKey(
                $this->foreignKey('created_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_projects_created_by')
            )
            ->update();

        $this->table('statuses')
            ->addForeignKey(
                $this->foreignKey('project_id')
                    ->setReferencedTable('projects')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_statuses_project_id')
            )
            ->update();

        $this->table('wiki_page_links')
            ->addForeignKey(
                $this->foreignKey('source_page_id')
                    ->setReferencedTable('wiki_pages')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wiki_page_links_source_page_id')
            )
            ->addForeignKey(
                $this->foreignKey('target_page_id')
                    ->setReferencedTable('wiki_pages')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wiki_page_links_target_page_id')
            )
            ->update();

        $this->table('wiki_page_revisions')
            ->addForeignKey(
                $this->foreignKey('edited_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wiki_page_revisions_edited_by')
            )
            ->addForeignKey(
                $this->foreignKey('wiki_page_id')
                    ->setReferencedTable('wiki_pages')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wiki_page_revisions_page_id')
            )
            ->update();

        $this->table('wiki_pages')
            ->addForeignKey(
                $this->foreignKey('created_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wiki_pages_created_by')
            )
            ->addForeignKey(
                $this->foreignKey('last_edited_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('SET_NULL')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wiki_pages_last_edited_by')
            )
            ->addForeignKey(
                $this->foreignKey('parent_id')
                    ->setReferencedTable('wiki_pages')
                    ->setReferencedColumns('id')
                    ->setOnDelete('SET_NULL')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wiki_pages_parent_id')
            )
            ->addForeignKey(
                $this->foreignKey('project_id')
                    ->setReferencedTable('projects')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wiki_pages_project_id')
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     *
     * @return void
     */
    public function down(): void
    {
        $this->table('activity_log')
            ->dropForeignKey(
                'actor_id'
            )
            ->dropForeignKey(
                'project_id'
            )->save();

        $this->table('attachment_links')
            ->dropForeignKey(
                'attachment_id'
            )->save();

        $this->table('attachments')
            ->dropForeignKey(
                'project_id'
            )
            ->dropForeignKey(
                'uploaded_by'
            )->save();

        $this->table('comments')
            ->dropForeignKey(
                'issue_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('departments')
            ->dropForeignKey(
                'project_id'
            )->save();

        $this->table('issue_relations')
            ->dropForeignKey(
                'issue_id'
            )
            ->dropForeignKey(
                'related_issue_id'
            )->save();

        $this->table('issues')
            ->dropForeignKey(
                'assignee_id'
            )
            ->dropForeignKey(
                'created_by'
            )
            ->dropForeignKey(
                'department_id'
            )
            ->dropForeignKey(
                'parent_id'
            )
            ->dropForeignKey(
                'project_id'
            )
            ->dropForeignKey(
                'status_id'
            )->save();

        $this->table('project_members')
            ->dropForeignKey(
                'project_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('projects')
            ->dropForeignKey(
                'created_by'
            )->save();

        $this->table('statuses')
            ->dropForeignKey(
                'project_id'
            )->save();

        $this->table('wiki_page_links')
            ->dropForeignKey(
                'source_page_id'
            )
            ->dropForeignKey(
                'target_page_id'
            )->save();

        $this->table('wiki_page_revisions')
            ->dropForeignKey(
                'edited_by'
            )
            ->dropForeignKey(
                'wiki_page_id'
            )->save();

        $this->table('wiki_pages')
            ->dropForeignKey(
                'created_by'
            )
            ->dropForeignKey(
                'last_edited_by'
            )
            ->dropForeignKey(
                'parent_id'
            )
            ->dropForeignKey(
                'project_id'
            )->save();

        $this->table('activity_log')->drop()->save();
        $this->table('attachment_links')->drop()->save();
        $this->table('attachments')->drop()->save();
        $this->table('comments')->drop()->save();
        $this->table('departments')->drop()->save();
        $this->table('issue_relations')->drop()->save();
        $this->table('issues')->drop()->save();
        $this->table('project_members')->drop()->save();
        $this->table('projects')->drop()->save();
        $this->table('statuses')->drop()->save();
        $this->table('users')->drop()->save();
        $this->table('wiki_page_links')->drop()->save();
        $this->table('wiki_page_revisions')->drop()->save();
        $this->table('wiki_pages')->drop()->save();
    }
}
