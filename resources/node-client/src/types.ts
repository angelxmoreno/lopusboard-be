export interface CollectionMeta {
    [key: string]: unknown;
}

export interface ErrorResponse {
    message: string;
    errors?: Record<string, unknown>;
    [key: string]: unknown;
}

export interface RemoteIdentity {
    provider: string;
    providerUserId: string;
    email?: string | null;
    emailVerified: boolean;
    displayName?: string | null;
    avatarUrl?: string | null;
    claims: Record<string, unknown>;
}

export interface UserSummary {
    id: number;
    name: string;
    email: string;
    avatarUrl: string | null;
}

export interface IdentityMeResponse {
    data: {
        remoteIdentity: RemoteIdentity;
        user: UserSummary;
    };
}

export interface Project {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    created_by: number;
    created: string;
    modified: string;
}

export interface ProjectUpdateInput {
    name?: string;
    slug?: string;
    description?: string | null;
}

export interface ProjectMember {
    id: number;
    project_id: number;
    user_id: number;
    role: 'admin' | 'member';
    created: string;
    modified: string;
}

export interface ProjectMemberCreateInput {
    project_id: number;
    user_id: number;
    role: 'admin' | 'member';
}

export interface ProjectMemberUpdateInput {
    role?: 'admin' | 'member';
}

export interface Status {
    id: number;
    project_id: number;
    name: string;
    color: string;
    position: number;
    is_default: boolean;
    is_done: boolean;
    created: string;
    modified: string;
}

export interface StatusCreateInput {
    project_id: number;
    name: string;
    color?: string;
    position?: number;
    is_default?: boolean;
    is_done?: boolean;
}

export interface StatusUpdateInput {
    name?: string;
    color?: string;
    position?: number;
    is_default?: boolean;
    is_done?: boolean;
}

export interface Department {
    id: number;
    project_id: number;
    name: string;
    color: string | null;
    position: number;
    created: string;
    modified: string;
}

export interface DepartmentCreateInput {
    project_id: number;
    name: string;
    color?: string | null;
    position?: number;
}

export interface DepartmentUpdateInput {
    name?: string;
    color?: string | null;
    position?: number;
}

export interface Issue {
    id: number;
    project_id: number;
    parent_id: number | null;
    type: 'issue' | 'task';
    title: string;
    description: string | null;
    status_id: number;
    priority: 'low' | 'medium' | 'high' | 'critical';
    assignee_id: number | null;
    department_id: number | null;
    due_date: string | null;
    position: number;
    created_by: number;
    created: string;
    modified: string;
}

export interface IssueCreateInput {
    project_id: number;
    parent_id?: number | null;
    type: 'issue' | 'task';
    title: string;
    description?: string | null;
    status_id: number;
    priority?: 'low' | 'medium' | 'high' | 'critical';
    assignee_id?: number | null;
    department_id?: number | null;
    due_date?: string | null;
    position?: number;
}

export interface IssueUpdateInput {
    parent_id?: number | null;
    type?: 'issue' | 'task';
    title?: string;
    description?: string | null;
    status_id?: number;
    priority?: 'low' | 'medium' | 'high' | 'critical';
    assignee_id?: number | null;
    department_id?: number | null;
    due_date?: string | null;
    position?: number;
}

export interface Comment {
    id: number;
    issue_id: number;
    user_id: number;
    body: string;
    created: string;
    modified: string;
}

export interface CommentCreateInput {
    issue_id: number;
    body: string;
}

export interface CommentUpdateInput {
    body: string;
}

export interface IssueRelation {
    id: number;
    issue_id: number;
    related_issue_id: number;
    type: 'blocks' | 'depends_on' | 'duplicates' | 'relates_to';
    created: string;
    modified: string;
}

export interface IssueRelationCreateInput {
    issue_id: number;
    related_issue_id: number;
    type: 'blocks' | 'depends_on' | 'duplicates' | 'relates_to';
}

export interface IssueRelationUpdateInput {
    related_issue_id?: number;
    type?: 'blocks' | 'depends_on' | 'duplicates' | 'relates_to';
}

export interface WikiPage {
    id: number;
    project_id: number;
    parent_id: number | null;
    title: string;
    slug: string;
    body: string | null;
    position: number;
    created_by: number;
    last_edited_by: number | null;
    created: string;
    modified: string;
}

export interface WikiPageCreateInput {
    project_id: number;
    parent_id?: number | null;
    title: string;
    slug?: string | null;
    body?: string | null;
    position?: number;
}

export interface WikiPageUpdateInput {
    parent_id?: number | null;
    title?: string;
    slug?: string;
    body?: string | null;
    position?: number;
}

export interface WikiPageRevision {
    id: number;
    wiki_page_id: number;
    body: string;
    revision_number: number;
    edited_by: number;
    created: string;
    modified: string;
}

export interface Attachment {
    id: number;
    project_id: number;
    storage_provider: 'google_drive' | 'minio';
    filename: string;
    mime_type: string | null;
    file_size: number | null;
    storage_path: string | null;
    external_url: string | null;
    external_id: string | null;
    uploaded_by: number;
    created: string;
    modified: string;
}

export interface AttachmentCreateInput {
    project_id: number;
    storage_provider?: 'google_drive' | 'minio';
    filename: string;
    mime_type?: string | null;
    file_size?: number | null;
    storage_path?: string | null;
    external_url?: string | null;
    external_id?: string | null;
}

export interface AttachmentUpdateInput {
    storage_provider?: 'google_drive' | 'minio';
    filename?: string;
    mime_type?: string | null;
    file_size?: number | null;
    storage_path?: string | null;
    external_url?: string | null;
    external_id?: string | null;
}

export interface ActivityLogEntry {
    id: number;
    project_id: number;
    actor_id: number;
    subject_type: 'issue' | 'task' | 'wiki_page' | 'comment' | 'attachment' | 'member';
    subject_id: number;
    action:
        | 'created'
        | 'updated'
        | 'deleted'
        | 'status_changed'
        | 'assigned'
        | 'unassigned'
        | 'priority_changed'
        | 'relation_added'
        | 'relation_removed'
        | 'comment_added'
        | 'file_attached'
        | 'moved';
    old_value: string | null;
    new_value: string | null;
    created: string;
    modified: string;
}

export interface CollectionResponse<T> {
    data: T[];
    meta?: CollectionMeta;
}

export interface SingleResponse<T> {
    data: T;
}
