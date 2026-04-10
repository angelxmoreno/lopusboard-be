import { ApiError } from './errors';
import type { ErrorResponse } from './types';

export type TokenProvider = () => string | null | Promise<string | null>;

export interface ClientOptions {
    baseUrl: string;
    getToken?: string | TokenProvider;
}

interface CacheEntry {
    etag: string;
    // biome-ignore lint/suspicious/noExplicitAny: generic transport data
    data: any;
}

export class BaseClient {
    protected baseUrl: string;
    protected getToken?: string | TokenProvider;
    private cache: Map<string, CacheEntry> = new Map();

    constructor(options: ClientOptions) {
        this.baseUrl = options.baseUrl.replace(/\/$/, '');
        this.getToken = options.getToken;
    }

    private async getAuthHeader(): Promise<string | null> {
        if (typeof this.getToken === 'function') {
            const token = await this.getToken();
            return token ? `Bearer ${token}` : null;
        }
        return this.getToken ? `Bearer ${this.getToken}` : null;
    }

    protected async request<T>(
        method: string,
        path: string,
        // biome-ignore lint/suspicious/noExplicitAny: generic request body
        body?: any,
        options: { headers?: Record<string, string> } = {}
    ): Promise<T> {
        const url = `${this.baseUrl}${path}`;
        const headers = new Headers(options.headers);
        const authHeader = await this.getAuthHeader();

        if (authHeader) {
            headers.set('Authorization', authHeader);
        }

        if (body !== undefined) {
            headers.set('Content-Type', 'application/json');
        }

        const isGet = method.toUpperCase() === 'GET';
        const cacheKey = `${method}:${authHeader ?? 'anon'}:${url}`;

        if (isGet) {
            const cached = this.cache.get(cacheKey);
            if (cached) {
                headers.set('If-None-Match', cached.etag);
            }
        }

        const response = await fetch(url, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });

        if (response.status === 304 && isGet) {
            const cached = this.cache.get(cacheKey);
            if (cached) {
                return cached.data as T;
            }
        }

        if (!response.ok) {
            let errorData: ErrorResponse | undefined;
            try {
                errorData = (await response.json()) as ErrorResponse;
            } catch {
                // Fallback if not JSON
            }
            throw new ApiError(response.status, errorData);
        }

        if (response.status === 204) {
            // biome-ignore lint/suspicious/noExplicitAny: 204 has no body
            return undefined as any;
        }

        const data = await response.json();

        if (isGet) {
            const etag = response.headers.get('ETag');
            if (etag) {
                this.cache.set(cacheKey, { etag, data });
            }
        }

        return data as T;
    }

    protected _get<T>(path: string, headers?: Record<string, string>) {
        return this.request<T>('GET', path, undefined, { headers });
    }

    // biome-ignore lint/suspicious/noExplicitAny: generic request body
    protected _post<T>(path: string, body?: any, headers?: Record<string, string>) {
        return this.request<T>('POST', path, body, { headers });
    }

    // biome-ignore lint/suspicious/noExplicitAny: generic request body
    protected _put<T>(path: string, body?: any, headers?: Record<string, string>) {
        return this.request<T>('PUT', path, body, { headers });
    }

    // biome-ignore lint/suspicious/noExplicitAny: generic request body
    protected _patch<T>(path: string, body?: any, headers?: Record<string, string>) {
        return this.request<T>('PATCH', path, body, { headers });
    }

    protected _delete<T>(path: string, headers?: Record<string, string>) {
        return this.request<T>('DELETE', path, undefined, { headers });
    }
}
