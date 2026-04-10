import type { ClientOptions } from './client';
import { ActivityLogResource } from './resources/activity-log';
import { AttachmentsResource } from './resources/attachments';
import { CommentsResource } from './resources/comments';
import { DepartmentsResource } from './resources/departments';
import { IdentityResource } from './resources/identity';
import { IssueRelationsResource } from './resources/issue-relations';
import { IssuesResource } from './resources/issues';
import { ProjectMembersResource } from './resources/project-members';
import { ProjectsResource } from './resources/projects';
import { StatusesResource } from './resources/statuses';
import { WikiPageRevisionsResource } from './resources/wiki-page-revisions';
import { WikiPagesResource } from './resources/wiki-pages';

export * from './client';
export * from './errors';
export * from './types';

export class LopusboardClient {
    public identity: IdentityResource;
    public projects: ProjectsResource;
    public projectMembers: ProjectMembersResource;
    public statuses: StatusesResource;
    public departments: DepartmentsResource;
    public issues: IssuesResource;
    public comments: CommentsResource;
    public issueRelations: IssueRelationsResource;
    public wikiPages: WikiPagesResource;
    public wikiPageRevisions: WikiPageRevisionsResource;
    public attachments: AttachmentsResource;
    public activityLog: ActivityLogResource;

    constructor(options: ClientOptions) {
        this.identity = new IdentityResource(options);
        this.projects = new ProjectsResource(options);
        this.projectMembers = new ProjectMembersResource(options);
        this.statuses = new StatusesResource(options);
        this.departments = new DepartmentsResource(options);
        this.issues = new IssuesResource(options);
        this.comments = new CommentsResource(options);
        this.issueRelations = new IssueRelationsResource(options);
        this.wikiPages = new WikiPagesResource(options);
        this.wikiPageRevisions = new WikiPageRevisionsResource(options);
        this.attachments = new AttachmentsResource(options);
        this.activityLog = new ActivityLogResource(options);
    }
}
