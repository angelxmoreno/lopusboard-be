import { BaseClient } from '../client';
import type { CollectionResponse, Project, ProjectUpdateInput, SingleResponse } from '../types';

export class ProjectsResource extends BaseClient {
    public list(): Promise<CollectionResponse<Project>> {
        return this._get('/api/projects');
    }

    public get(id: number): Promise<SingleResponse<Project>> {
        return this._get(`/api/projects/${id}`);
    }

    public update(id: number, data: ProjectUpdateInput): Promise<SingleResponse<Project>> {
        return this._patch(`/api/projects/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/projects/${id}`);
    }
}
