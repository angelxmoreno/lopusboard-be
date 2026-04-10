import type { ErrorResponse } from './types';

export class ApiError extends Error {
    public status: number;
    public data?: ErrorResponse;

    constructor(status: number, data?: ErrorResponse) {
        const message = data?.message || `API Error: ${status}`;
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.data = data;
    }
}
