import { BaseClient } from '../client';
import type {
    CollectionResponse,
    ProjectMember,
    ProjectMemberCreateInput,
    ProjectMemberUpdateInput,
    SingleResponse,
} from '../types';

export class ProjectMembersResource extends BaseClient {
    public list(): Promise<CollectionResponse<ProjectMember>> {
        return this._get('/api/project-members');
    }

    public create(data: ProjectMemberCreateInput): Promise<SingleResponse<ProjectMember>> {
        return this._post('/api/project-members', data);
    }

    public get(id: number): Promise<SingleResponse<ProjectMember>> {
        return this._get(`/api/project-members/${id}`);
    }

    public update(id: number, data: ProjectMemberUpdateInput): Promise<SingleResponse<ProjectMember>> {
        return this._patch(`/api/project-members/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/project-members/${id}`);
    }
}
