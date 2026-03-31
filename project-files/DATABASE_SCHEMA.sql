-- LopusBoard backend schema for MySQL 8
-- Conventions:
-- - Table names use plural snake_case.
-- - Primary keys use id INT UNSIGNED AUTO_INCREMENT.
-- - Foreign keys use singular_table_id.
-- - Timestamps use created and modified DATETIME columns.
-- - Ordered lists use DECIMAL(10,5) positions.
-- - Markdown bodies use LONGTEXT.

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- 1. AUTH AND MEMBERSHIP
-- ---------------------------------------------------------------------------

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appwrite_id VARCHAR(128) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  avatar_url VARCHAR(512),
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  created_by INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  CONSTRAINT fk_projects_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_members (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  UNIQUE KEY uq_project_member (project_id, user_id),
  CONSTRAINT fk_project_members_project_id
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_project_members_user_id
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. PROJECT LOOKUPS
-- ---------------------------------------------------------------------------

CREATE TABLE statuses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  color VARCHAR(7) NOT NULL DEFAULT '#6B7280',
  position DECIMAL(10,5) NOT NULL DEFAULT 0,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  is_done TINYINT(1) NOT NULL DEFAULT 0,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  CONSTRAINT fk_statuses_project_id
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE departments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  color VARCHAR(7),
  position DECIMAL(10,5) NOT NULL DEFAULT 0,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  CONSTRAINT fk_departments_project_id
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 3. ISSUES AND TASKS
-- ---------------------------------------------------------------------------

CREATE TABLE issues (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED,
  type ENUM('issue', 'task') NOT NULL DEFAULT 'issue',
  title VARCHAR(500) NOT NULL,
  description LONGTEXT,
  status_id INT UNSIGNED NOT NULL,
  priority ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
  assignee_id INT UNSIGNED,
  department_id INT UNSIGNED,
  due_date DATE,
  position DECIMAL(10,5) NOT NULL DEFAULT 0,
  created_by INT UNSIGNED NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE issue_relations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_id INT UNSIGNED NOT NULL,
  related_issue_id INT UNSIGNED NOT NULL,
  type ENUM('blocks', 'depends_on', 'duplicates', 'relates_to') NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  UNIQUE KEY uq_relation (issue_id, related_issue_id, type),
  CONSTRAINT fk_issue_relations_issue_id
    FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
  CONSTRAINT fk_issue_relations_related_issue_id
    FOREIGN KEY (related_issue_id) REFERENCES issues(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  body LONGTEXT NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  CONSTRAINT fk_comments_issue_id
    FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user_id
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 4. WIKI
-- ---------------------------------------------------------------------------

CREATE TABLE wiki_pages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED,
  title VARCHAR(500) NOT NULL,
  slug VARCHAR(500) NOT NULL,
  body LONGTEXT,
  position DECIMAL(10,5) NOT NULL DEFAULT 0,
  created_by INT UNSIGNED NOT NULL,
  last_edited_by INT UNSIGNED,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  UNIQUE KEY uq_wiki_slug (project_id, slug),
  CONSTRAINT fk_wiki_pages_project_id
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_wiki_pages_parent_id
    FOREIGN KEY (parent_id) REFERENCES wiki_pages(id) ON DELETE SET NULL,
  CONSTRAINT fk_wiki_pages_created_by
    FOREIGN KEY (created_by) REFERENCES users(id),
  CONSTRAINT fk_wiki_pages_last_edited_by
    FOREIGN KEY (last_edited_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wiki_page_revisions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  wiki_page_id INT UNSIGNED NOT NULL,
  body LONGTEXT NOT NULL,
  revision_number INT UNSIGNED NOT NULL,
  edited_by INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  CONSTRAINT fk_wiki_page_revisions_page_id
    FOREIGN KEY (wiki_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE,
  CONSTRAINT fk_wiki_page_revisions_edited_by
    FOREIGN KEY (edited_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wiki_page_links (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_page_id INT UNSIGNED NOT NULL,
  target_page_id INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  UNIQUE KEY uq_wiki_link (source_page_id, target_page_id),
  CONSTRAINT fk_wiki_page_links_source_page_id
    FOREIGN KEY (source_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE,
  CONSTRAINT fk_wiki_page_links_target_page_id
    FOREIGN KEY (target_page_id) REFERENCES wiki_pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 5. FILES AND ATTACHMENTS
-- ---------------------------------------------------------------------------

CREATE TABLE attachments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  storage_provider ENUM('google_drive', 'minio') NOT NULL DEFAULT 'google_drive',
  filename VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100),
  file_size BIGINT UNSIGNED,
  storage_path VARCHAR(1000),
  external_url VARCHAR(1000),
  external_id VARCHAR(255),
  uploaded_by INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  CONSTRAINT fk_attachments_project_id
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_attachments_uploaded_by
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attachment_links (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attachment_id INT UNSIGNED NOT NULL,
  linkable_type ENUM('issue', 'wiki_page') NOT NULL,
  linkable_id INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  CONSTRAINT fk_attachment_links_attachment_id
    FOREIGN KEY (attachment_id) REFERENCES attachments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 6. ACTIVITY AND AUDIT
-- ---------------------------------------------------------------------------

CREATE TABLE activity_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  actor_id INT UNSIGNED NOT NULL,
  subject_type ENUM('issue', 'task', 'wiki_page', 'comment', 'attachment', 'member') NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  action ENUM(
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
  ) NOT NULL,
  old_value JSON,
  new_value JSON,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  KEY idx_activity_subject (project_id, subject_type, subject_id),
  KEY idx_activity_project (project_id, created),
  CONSTRAINT fk_activity_log_project_id
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_activity_log_actor_id
    FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
