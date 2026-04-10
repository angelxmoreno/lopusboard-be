import { BaseClient } from '../client';
import type {
    CollectionResponse,
    IssueRelation,
    IssueRelationCreateInput,
    IssueRelationUpdateInput,
    SingleResponse,
} from '../types';

export class IssueRelationsResource extends BaseClient {
    public list(): Promise<CollectionResponse<IssueRelation>> {
        return this._get('/api/issue-relations');
    }

    public create(data: IssueRelationCreateInput): Promise<SingleResponse<IssueRelation>> {
        return this._post('/api/issue-relations', data);
    }

    public get(id: number): Promise<SingleResponse<IssueRelation>> {
        return this._get(`/api/issue-relations/${id}`);
    }

    public update(id: number, data: IssueRelationUpdateInput): Promise<SingleResponse<IssueRelation>> {
        return this._patch(`/api/issue-relations/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/issue-relations/${id}`);
    }
}
