PRAGMA foreign_keys = ON;

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appwrite_id TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    avatar_url TEXT,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL
);

CREATE TABLE projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    description TEXT,
    created_by INTEGER NOT NULL,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT fk_projects_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE project_members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    role TEXT NOT NULL DEFAULT 'member' CHECK (role IN ('admin', 'member')),
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT uq_project_member UNIQUE (project_id, user_id),
    CONSTRAINT fk_project_members_project_id
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_members_user_id
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE statuses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    color TEXT NOT NULL DEFAULT '#6B7280',
    position DECIMAL(10,5) NOT NULL DEFAULT 0,
    is_default INTEGER NOT NULL DEFAULT 0,
    is_done INTEGER NOT NULL DEFAULT 0,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT fk_statuses_project_id
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE departments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    color TEXT,
    position DECIMAL(10,5) NOT NULL DEFAULT 0,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT fk_departments_project_id
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE issues (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    parent_id INTEGER,
    type TEXT NOT NULL DEFAULT 'issue' CHECK (type IN ('issue', 'task')),
    title TEXT NOT NULL,
    description TEXT,
    status_id INTEGER NOT NULL,
    priority TEXT NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high', 'critical')),
    assignee_id INTEGER,
    department_id INTEGER,
    due_date DATE,
    position DECIMAL(10,5) NOT NULL DEFAULT 0,
    created_by INTEGER NOT NULL,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT fk_issues_project_id
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_issues_parent_id
        FOREIGN KEY (parent_id) REFERENCES issues(id) ON DELETE CASCADE,
    CONSTRAINT fk_issues_status_id
        FOREIGN KEY (status_id) REFERENCES statuses(id),
    CONSTRAINT fk_issues_assignee_id
        FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_issues_department_id
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    CONSTRAINT fk_issues_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE issue_relations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    issue_id INTEGER NOT NULL,
    related_issue_id INTEGER NOT NULL,
    type TEXT NOT NULL CHECK (type IN ('blocks', 'depends_on', 'duplicates', 'relates_to')),
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT uq_relation UNIQUE (issue_id, related_issue_id, type),
    CONSTRAINT fk_issue_relations_issue_id
        FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
    CONSTRAINT fk_issue_relations_related_issue_id
        FOREIGN KEY (related_issue_id) REFERENCES issues(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    issue_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    body TEXT NOT NULL,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT fk_comments_issue_id
        FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user_id
        FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE wiki_pages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    parent_id INTEGER,
    title TEXT NOT NULL,
    slug TEXT NOT NULL,
    body TEXT,
    position DECIMAL(10,5) NOT NULL DEFAULT 0,
    created_by INTEGER NOT NULL,
    last_edited_by INTEGER,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT uq_wiki_slug UNIQUE (project_id, slug),
    CONSTRAINT fk_wiki_pages_project_id
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_wiki_pages_parent_id
        FOREIGN KEY (parent_id) REFERENCES wiki_pages(id) ON DELETE SET NULL,
    CONSTRAINT fk_wiki_pages_created_by
        FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_wiki_pages_last_edited_by
        FOREIGN KEY (last_edited_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE wiki_page_revisions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    wiki_page_id INTEGER NOT NULL,
    body TEXT NOT NULL,
    revision_number INTEGER NOT NULL,
    edited_by INTEGER NOT NULL,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT uq_wiki_page_revision UNIQUE (wiki_page_id, revision_number),
    CONSTRAINT fk_wiki_page_revisions_page_id
        FOREIGN KEY (wiki_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE,
    CONSTRAINT fk_wiki_page_revisions_edited_by
        FOREIGN KEY (edited_by) REFERENCES users(id)
);

CREATE TABLE wiki_page_links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source_page_id INTEGER NOT NULL,
    target_page_id INTEGER NOT NULL,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT uq_wiki_link UNIQUE (source_page_id, target_page_id),
    CONSTRAINT fk_wiki_page_links_source_page_id
        FOREIGN KEY (source_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE,
    CONSTRAINT fk_wiki_page_links_target_page_id
        FOREIGN KEY (target_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE
);

CREATE TABLE attachments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    storage_provider TEXT NOT NULL DEFAULT 'google_drive' CHECK (storage_provider IN ('google_drive', 'minio')),
    filename TEXT NOT NULL,
    mime_type TEXT,
    file_size INTEGER,
    storage_path TEXT,
    external_url TEXT,
    external_id TEXT,
    uploaded_by INTEGER NOT NULL,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT fk_attachments_project_id
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_attachments_uploaded_by
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE attachment_links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    attachment_id INTEGER NOT NULL,
    linkable_type TEXT NOT NULL CHECK (linkable_type IN ('issue', 'wiki_page')),
    linkable_id INTEGER NOT NULL,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT fk_attachment_links_attachment_id
        FOREIGN KEY (attachment_id) REFERENCES attachments(id) ON DELETE CASCADE
);

CREATE TABLE activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    actor_id INTEGER NOT NULL,
    subject_type TEXT NOT NULL CHECK (subject_type IN ('issue', 'task', 'wiki_page', 'comment', 'attachment', 'member')),
    subject_id INTEGER NOT NULL,
    action TEXT NOT NULL CHECK (
        action IN (
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
            'moved'
        )
    ),
    old_value TEXT,
    new_value TEXT,
    created DATETIME NOT NULL,
    modified DATETIME NOT NULL,
    CONSTRAINT fk_activity_log_project_id
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_activity_log_actor_id
        FOREIGN KEY (actor_id) REFERENCES users(id)
);

CREATE INDEX idx_activity_subject ON activity_log (project_id, subject_type, subject_id);
CREATE INDEX idx_activity_project ON activity_log (project_id, created);
CREATE INDEX idx_attachment_links_linkable ON attachment_links (linkable_type, linkable_id);
