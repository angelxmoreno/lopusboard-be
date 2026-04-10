import { afterEach, beforeEach, describe, expect, type Mock, mock, test } from 'bun:test';
import { ApiError, LopusboardClient } from '../src';

describe('LopusboardClient', () => {
    const baseUrl = 'http://api.test';
    const token = 'test-token';
    const originalFetch = global.fetch;

    beforeEach(() => {
        // Reset fetch mock for each test
        global.fetch = mock(async (url: string | URL | Request, _init?: RequestInit) => {
            const urlString = url.toString();

            if (urlString === `${baseUrl}/api/identity/me`) {
                return new Response(JSON.stringify({ data: { user: { id: 1, name: 'Test' } } }), {
                    status: 200,
                    headers: { 'Content-Type': 'application/json', ETag: 'W/"123"' },
                });
            }
            if (urlString === `${baseUrl}/api/projects/1`) {
                return new Response(JSON.stringify({ data: { id: 1 } }), {
                    status: 200,
                    headers: { 'Content-Type': 'application/json', ETag: 'W/"123"' },
                });
            }
            if (urlString === `${baseUrl}/api/projects/999`) {
                return new Response(JSON.stringify({ message: 'Not Found' }), {
                    status: 404,
                    headers: { 'Content-Type': 'application/json' },
                });
            }
            return new Response(null, { status: 404 });
        }) as unknown as typeof fetch;
    });

    afterEach(() => {
        global.fetch = originalFetch;
    });

    test('should inject auth header', async () => {
        const client = new LopusboardClient({ baseUrl, getToken: token });

        await client.identity.me();

        expect(global.fetch).toHaveBeenCalled();
        const mockedFetch = global.fetch as unknown as Mock<typeof fetch>;
        const call = mockedFetch.mock.calls[0];
        const headers = call?.[1]?.headers as Headers;
        expect(headers.get('Authorization')).toBe(`Bearer ${token}`);
    });

    test('should handle ETag and 304', async () => {
        const client = new LopusboardClient({ baseUrl });

        // First call - sets cache
        const res1 = await client.projects.get(1);
        expect(res1.data.id).toBe(1);
        expect(global.fetch).toHaveBeenCalledTimes(1);

        // Mock 304 for the next call
        global.fetch = mock(async (_url: string | URL | Request, init?: RequestInit) => {
            const headers = init?.headers as Headers;
            if (headers.get('If-None-Match') === 'W/"123"') {
                return new Response(null, { status: 304 });
            }
            return new Response(null, { status: 500 });
        }) as unknown as typeof fetch;

        // Second call - should send If-None-Match and return cached data
        const res2 = await client.projects.get(1);
        expect(res2.data.id).toBe(1);
        expect(global.fetch).toHaveBeenCalledTimes(1);
        const mockedFetch = global.fetch as unknown as Mock<typeof fetch>;
        const call = mockedFetch.mock.calls[0];
        const headers = call?.[1]?.headers as Headers;
        expect(headers.get('If-None-Match')).toBe('W/"123"');
    });

    test('should isolate cache by token', async () => {
        let currentToken = 'token-1';
        const client = new LopusboardClient({
            baseUrl,
            getToken: () => currentToken,
        });

        // Populate cache for token 1
        await client.projects.get(1);
        expect(global.fetch).toHaveBeenCalledTimes(1);

        // Change token but use SAME client instance
        currentToken = 'token-2';

        // Should NOT send If-None-Match because token changed, triggering a new request
        await client.projects.get(1);
        expect(global.fetch).toHaveBeenCalledTimes(2);

        const mockedFetch = global.fetch as unknown as Mock<typeof fetch>;
        const secondCall = mockedFetch.mock.calls[1];
        const headers = secondCall?.[1]?.headers as Headers;
        expect(headers.get('If-None-Match')).toBeNull();
    });

    test('should throw ApiError on failure', async () => {
        const client = new LopusboardClient({ baseUrl });

        try {
            await client.projects.get(999);
            expect(true).toBe(false); // Should not reach here
        } catch (error) {
            expect(error).toBeInstanceOf(ApiError);
            const apiError = error as ApiError;
            expect(apiError.status).toBe(404);
            expect(apiError.data?.message).toBe('Not Found');
        }
    });
});
