import { BaseClient } from '../client';
import type { CollectionResponse, SingleResponse, Status, StatusCreateInput, StatusUpdateInput } from '../types';

export class StatusesResource extends BaseClient {
    public list(): Promise<CollectionResponse<Status>> {
        return this._get('/api/statuses');
    }

    public create(data: StatusCreateInput): Promise<SingleResponse<Status>> {
        return this._post('/api/statuses', data);
    }

    public get(id: number): Promise<SingleResponse<Status>> {
        return this._get(`/api/statuses/${id}`);
    }

    public update(id: number, data: StatusUpdateInput): Promise<SingleResponse<Status>> {
        return this._patch(`/api/statuses/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/statuses/${id}`);
    }
}
