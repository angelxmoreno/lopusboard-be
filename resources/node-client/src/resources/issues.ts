import { BaseClient } from '../client';
import type { CollectionResponse, Issue, IssueCreateInput, IssueUpdateInput, SingleResponse } from '../types';

export class IssuesResource extends BaseClient {
    public list(): Promise<CollectionResponse<Issue>> {
        return this._get('/api/issues');
    }

    public create(data: IssueCreateInput): Promise<SingleResponse<Issue>> {
        return this._post('/api/issues', data);
    }

    public get(id: number): Promise<SingleResponse<Issue>> {
        return this._get(`/api/issues/${id}`);
    }

    public update(id: number, data: IssueUpdateInput): Promise<SingleResponse<Issue>> {
        return this._patch(`/api/issues/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/issues/${id}`);
    }
}
