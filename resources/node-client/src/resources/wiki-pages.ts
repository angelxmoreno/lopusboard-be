import { BaseClient } from '../client';
import type { CollectionResponse, SingleResponse, WikiPage, WikiPageCreateInput, WikiPageUpdateInput } from '../types';

export class WikiPagesResource extends BaseClient {
    public list(): Promise<CollectionResponse<WikiPage>> {
        return this._get('/api/wiki-pages');
    }

    public create(data: WikiPageCreateInput): Promise<SingleResponse<WikiPage>> {
        return this._post('/api/wiki-pages', data);
    }

    public get(id: number): Promise<SingleResponse<WikiPage>> {
        return this._get(`/api/wiki-pages/${id}`);
    }

    public update(id: number, data: WikiPageUpdateInput): Promise<SingleResponse<WikiPage>> {
        return this._patch(`/api/wiki-pages/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/wiki-pages/${id}`);
    }
}
