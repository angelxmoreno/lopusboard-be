# Database Schema & Conventions (CakePHP 5)

## Conventions
- Table names: Plural `snake_case` (e.g., `issue_relations`).
- Primary key: `id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`.
- Foreign keys: `singular_table_id` (e.g., `project_id`).
- Timestamps: `created DATETIME`, `modified DATETIME` (auto-managed).
- Floats for ordering: `DECIMAL(10,5)` for Kanban position.
- Long text: `LONGTEXT` for markdown content.

## Schema Definition (MySQL)

```sql
-- ─────────────────────────────────────────────────
-- 1. AUTH & MEMBERSHIP
-- ─────────────────────────────────────────────────

CREATE TABLE users (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appwrite_id  VARCHAR(128) NOT NULL UNIQUE,
  name         VARCHAR(255) NOT NULL,
  email        VARCHAR(255) NOT NULL UNIQUE,
  avatar_url   VARCHAR(512),
  created      DATETIME NOT NULL,
  modified     DATETIME NOT NULL
);

CREATE TABLE projects (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(255) NOT NULL,
  slug         VARCHAR(100) NOT NULL UNIQUE,
  description  TEXT,
  created_by   INT UNSIGNED NOT NULL,
  created      DATETIME NOT NULL,
  modified     DATETIME NOT NULL,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE project_members (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id  INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NOT NULL,
  role        ENUM('admin','member') NOT NULL DEFAULT 'member',
  created     DATETIME NOT NULL,
  modified    DATETIME NOT NULL,
  UNIQUE KEY uq_project_member (project_id, user_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
);

-- ─────────────────────────────────────────────────
-- 2. PROJECT LOOKUPS
-- ─────────────────────────────────────────────────

CREATE TABLE statuses (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id  INT UNSIGNED NOT NULL,
  name        VARCHAR(100) NOT NULL,
  color       VARCHAR(7)   NOT NULL DEFAULT '#6B7280',
  position    DECIMAL(10,5) NOT NULL DEFAULT 0,
  is_default  TINYINT(1)   NOT NULL DEFAULT 0,
  is_done     TINYINT(1)   NOT NULL DEFAULT 0,
  created     DATETIME NOT NULL,
  modified    DATETIME NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE departments (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id  INT UNSIGNED NOT NULL,
  name        VARCHAR(100) NOT NULL,
  color       VARCHAR(7),
  position    DECIMAL(10,5) NOT NULL DEFAULT 0,
  created     DATETIME NOT NULL,
  modified    DATETIME NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────────────
-- 3. ISSUES & TASKS
-- ─────────────────────────────────────────────────

CREATE TABLE issues (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id    INT UNSIGNED NOT NULL,
  parent_id     INT UNSIGNED,
  type          ENUM('issue','task') NOT NULL DEFAULT 'issue',
  title         VARCHAR(500) NOT NULL,
  description   LONGTEXT,
  status_id     INT UNSIGNED NOT NULL,
  priority      ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  assignee_id   INT UNSIGNED,
  department_id INT UNSIGNED,
  due_date      DATE,
  position      DECIMAL(10,5) NOT NULL DEFAULT 0,
  created_by    INT UNSIGNED NOT NULL,
  created       DATETIME NOT NULL,
  modified      DATETIME NOT NULL,
  FOREIGN KEY (project_id)    REFERENCES projects(id)    ON DELETE CASCADE,
  FOREIGN KEY (parent_id)     REFERENCES issues(id)      ON DELETE CASCADE,
  FOREIGN KEY (status_id)     REFERENCES statuses(id),
  FOREIGN KEY (assignee_id)   REFERENCES users(id)       ON DELETE SET NULL,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by)    REFERENCES users(id)
);

CREATE TABLE issue_relations (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_id         INT UNSIGNED NOT NULL,
  related_issue_id INT UNSIGNED NOT NULL,
  type             ENUM('blocks','depends_on','duplicates','relates_to') NOT NULL,
  created          DATETIME NOT NULL,
  modified         DATETIME NOT NULL,
  UNIQUE KEY uq_relation (issue_id, related_issue_id, type),
  FOREIGN KEY (issue_id)         REFERENCES issues(id) ON DELETE CASCADE,
  FOREIGN KEY (related_issue_id) REFERENCES issues(id) ON DELETE CASCADE
);

CREATE TABLE comments (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_id  INT UNSIGNED NOT NULL,
  user_id   INT UNSIGNED NOT NULL,
  body      LONGTEXT NOT NULL,
  created   DATETIME NOT NULL,
  modified  DATETIME NOT NULL,
  FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)  REFERENCES users(id)
);

-- ─────────────────────────────────────────────────
-- 4. WIKI
-- ─────────────────────────────────────────────────

CREATE TABLE wiki_pages (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id     INT UNSIGNED NOT NULL,
  parent_id      INT UNSIGNED,
  title          VARCHAR(500) NOT NULL,
  slug           VARCHAR(500) NOT NULL,
  body           LONGTEXT,
  position       DECIMAL(10,5) NOT NULL DEFAULT 0,
  created_by     INT UNSIGNED NOT NULL,
  last_edited_by INT UNSIGNED,
  created        DATETIME NOT NULL,
  modified       DATETIME NOT NULL,
  UNIQUE KEY uq_wiki_slug (project_id, slug),
  FOREIGN KEY (project_id)     REFERENCES projects(id)   ON DELETE CASCADE,
  FOREIGN KEY (parent_id)      REFERENCES wiki_pages(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by)     REFERENCES users(id),
  FOREIGN KEY (last_edited_by) REFERENCES users(id)      ON DELETE SET NULL
);

CREATE TABLE wiki_page_revisions (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  wiki_page_id    INT UNSIGNED NOT NULL,
  body            LONGTEXT NOT NULL,
  revision_number INT UNSIGNED NOT NULL,
  edited_by       INT UNSIGNED NOT NULL,
  created         DATETIME NOT NULL,
  modified        DATETIME NOT NULL,
  FOREIGN KEY (wiki_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE,
  FOREIGN KEY (edited_by)    REFERENCES users(id)
);

CREATE TABLE wiki_page_links (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_page_id INT UNSIGNED NOT NULL,
  target_page_id INT UNSIGNED NOT NULL,
  created        DATETIME NOT NULL,
  modified       DATETIME NOT NULL,
  UNIQUE KEY uq_wiki_link (source_page_id, target_page_id),
  FOREIGN KEY (source_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE,
  FOREIGN KEY (target_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────────────
-- 5. FILES & ATTACHMENTS
-- ─────────────────────────────────────────────────

CREATE TABLE attachments (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id       INT UNSIGNED NOT NULL,
  storage_provider ENUM('google_drive','minio') NOT NULL DEFAULT 'google_drive',
  filename         VARCHAR(500) NOT NULL,
  mime_type        VARCHAR(100),
  file_size        BIGINT UNSIGNED,
  storage_path     VARCHAR(1000),
  external_url     VARCHAR(1000),
  external_id      VARCHAR(255),
  uploaded_by      INT UNSIGNED NOT NULL,
  created          DATETIME NOT NULL,
  modified         DATETIME NOT NULL,
  FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE attachment_links (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attachment_id INT UNSIGNED NOT NULL,
  linkable_type ENUM('issue','wiki_page') NOT NULL,
  linkable_id   INT UNSIGNED NOT NULL,
  created       DATETIME NOT NULL,
  modified      DATETIME NOT NULL,
  FOREIGN KEY (attachment_id) REFERENCES attachments(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────────────
-- 6. ACTIVITY & AUDIT
-- ─────────────────────────────────────────────────

CREATE TABLE activity_log (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id   INT UNSIGNED NOT NULL,
  actor_id     INT UNSIGNED NOT NULL,
  subject_type ENUM('issue','task','wiki_page','comment','attachment','member') NOT NULL,
  subject_id   INT UNSIGNED NOT NULL,
  action       ENUM('created','updated','deleted','status_changed','assigned',
                    'unassigned','priority_changed','relation_added',
                    'relation_removed','comment_added','file_attached','moved') NOT NULL,
  old_value    JSON,
  new_value    JSON,
  created      DATETIME NOT NULL,
  modified     DATETIME NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_id)   REFERENCES users(id),
  INDEX idx_activity_subject (project_id, subject_type, subject_id),
  INDEX idx_activity_project (project_id, created)
);
```
