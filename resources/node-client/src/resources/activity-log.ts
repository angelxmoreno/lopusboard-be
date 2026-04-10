import { BaseClient } from '../client';
import type { ActivityLogEntry, CollectionResponse } from '../types';

export class ActivityLogResource extends BaseClient {
    public list(): Promise<CollectionResponse<ActivityLogEntry>> {
        return this._get('/api/activity-log');
    }
}
