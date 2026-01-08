<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get all tasks with optional filtering and pagination
     */
    public function getAllTasks(array $filters = [], int $perPage = null): Collection|LengthAwarePaginator
    {
        return $this->taskRepository->all($filters, $perPage);
    }

    /**
     * Get task by ID
     */
    public function getTaskById(int $id)
    {
        return $this->taskRepository->find($id);
    }

    /**
     * Create a new task
     */
    public function createTask(array $data)
    {
        return $this->taskRepository->create($data);
    }

    /**
     * Update task
     */
    public function updateTask(int $id, array $data)
    {
        return $this->taskRepository->update($id, $data);
    }

    /**
     * Delete task
     */
    public function deleteTask(int $id): bool
    {
        return $this->taskRepository->delete($id);
    }
}
