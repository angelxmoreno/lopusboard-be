import { BaseClient } from '../client';
import type {
    Attachment,
    AttachmentCreateInput,
    AttachmentUpdateInput,
    CollectionResponse,
    SingleResponse,
} from '../types';

export class AttachmentsResource extends BaseClient {
    public list(): Promise<CollectionResponse<Attachment>> {
        return this._get('/api/attachments');
    }

    public create(data: AttachmentCreateInput): Promise<SingleResponse<Attachment>> {
        return this._post('/api/attachments', data);
    }

    public get(id: number): Promise<SingleResponse<Attachment>> {
        return this._get(`/api/attachments/${id}`);
    }

    public update(id: number, data: AttachmentUpdateInput): Promise<SingleResponse<Attachment>> {
        return this._patch(`/api/attachments/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/attachments/${id}`);
    }
}
