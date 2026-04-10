import { BaseClient } from '../client';
import type {
    CollectionResponse,
    Department,
    DepartmentCreateInput,
    DepartmentUpdateInput,
    SingleResponse,
} from '../types';

export class DepartmentsResource extends BaseClient {
    public list(): Promise<CollectionResponse<Department>> {
        return this._get('/api/departments');
    }

    public create(data: DepartmentCreateInput): Promise<SingleResponse<Department>> {
        return this._post('/api/departments', data);
    }

    public get(id: number): Promise<SingleResponse<Department>> {
        return this._get(`/api/departments/${id}`);
    }

    public update(id: number, data: DepartmentUpdateInput): Promise<SingleResponse<Department>> {
        return this._patch(`/api/departments/${id}`, data);
    }

    public delete(id: number): Promise<void> {
        return this._delete(`/api/departments/${id}`);
    }
}
