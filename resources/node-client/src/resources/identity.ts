import { BaseClient } from '../client';
import type { IdentityMeResponse } from '../types';

export class IdentityResource extends BaseClient {
    public me(): Promise<IdentityMeResponse> {
        return this._get('/api/identity/me');
    }
}
