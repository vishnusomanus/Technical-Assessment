<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a task (success case)
     */
    public function test_can_create_task_successfully(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'status' => 'pending',
            'due_date' => '2024-12-31',
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_date',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => [
                    'title' => 'Test Task',
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'status' => 'pending',
        ]);
    }

    /**
     * Test creating a task (validation failure)
     */
    public function test_cannot_create_task_without_title(): void
    {
        $taskData = [
            'description' => 'Task without title',
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test listing all tasks
     */
    public function test_can_list_all_tasks(): void
    {
        Task::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'due_date',
                    ],
                ],
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test updating a task
     */
    public function test_can_update_task(): void
    {
        $task = Task::factory()->create([
            'title' => 'Original Title',
            'status' => 'pending',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'in_progress',
        ];

        $response = $this->putJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => [
                    'title' => 'Updated Title',
                    'status' => 'in_progress',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => 'in_progress',
        ]);
    }

    /**
     * Test deleting a task
     */
    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);

        // Check soft delete (bonus feature)
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test filtering tasks by status (bonus feature)
     */
    public function test_can_filter_tasks_by_status(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'in_progress']);
        Task::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/v1/tasks?status=pending');

        $response->assertStatus(200);
        $tasks = $response->json('data');
        
        foreach ($tasks as $task) {
            $this->assertEquals('pending', $task['status']);
        }
    }

    /**
     * Test pagination (bonus feature)
     */
    public function test_tasks_are_paginated(): void
    {
        Task::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/tasks?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJson([
                'meta' => [
                    'per_page' => 10,
                    'total' => 15,
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
    }
}
