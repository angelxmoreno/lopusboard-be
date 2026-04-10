import { BaseClient } from '../client';
import type { CollectionResponse, Comment, CommentCreateInput, CommentUpdateInput, SingleResponse } from '../types';

export class CommentsResource extends BaseClient {
    public list(): Promise<CollectionResponse<Comment>> {
        return this._get('/api/comments');
    }

    public create(data: CommentCreateInput): Promise<SingleResponse<Comment>> {
        return this._post('/api/comments', data);
    }

    public get(id: number): Promise<SingleResponse<Comment>> {
        return this._get(`/api/comments/${id}`);
    }

    public update(id: number, data: CommentUpdateInput): Promise<SingleResponse<Comment>> {
        return this._patch(`/api/comments/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/comments/${id}`);
    }
}
