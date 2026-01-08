<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    protected $model;

    public function __construct(Task $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records with optional filtering and pagination
     */
    public function all(array $filters = [], int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Filter by status (bonus feature)
        if (isset($filters['status']) && in_array($filters['status'], ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $filters['status']);
        }

        // Pagination (bonus feature)
        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Find record by ID
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }

    /**
     * Create a new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update(int $id, array $data)
    {
        $record = $this->model->find($id);
        
        if ($record) {
            $record->update($data);
            return $record->fresh();
        }
        
        return null;
    }

    /**
     * Delete record (soft delete if enabled)
     */
    public function delete(int $id): bool
    {
        $record = $this->model->find($id);
        
        if ($record) {
            return $record->delete();
        }
        
        return false;
    }
}
