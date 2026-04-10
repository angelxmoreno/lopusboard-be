import { BaseClient } from '../client';
import type { CollectionResponse, SingleResponse, WikiPageRevision } from '../types';

export class WikiPageRevisionsResource extends BaseClient {
    public list(): Promise<CollectionResponse<WikiPageRevision>> {
        return this._get('/api/wiki-page-revisions');
    }

    public get(id: number): Promise<SingleResponse<WikiPageRevision>> {
        return this._get(`/api/wiki-page-revisions/${id}`);
    }
}
