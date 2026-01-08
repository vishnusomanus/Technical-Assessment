<template>
  <div class="tasks-container">
    <div class="header-section">
      <h2>Tasks Management</h2>
      <button @click="showAddForm = !showAddForm" class="btn btn-primary">
        {{ showAddForm ? 'Cancel' : 'Add New Task' }}
      </button>
    </div>

    <!-- Filter Section (Bonus Feature) -->
    <div class="filter-section">
      <label>Filter by Status:</label>
      <select v-model="statusFilter" @change="loadTasks" class="form-control filter-select">
        <option value="">All Tasks</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
      </select>
    </div>

    <!-- Add Task Form -->
    <div v-if="showAddForm" class="form-container">
      <h3>Add New Task</h3>
      <form @submit.prevent="addTask">
        <div class="form-group">
          <label>Title: <span class="required">*</span></label>
          <input v-model="newTask.title" type="text" required maxlength="255" class="form-control" />
        </div>
        <div class="form-group">
          <label>Description:</label>
          <textarea v-model="newTask.description" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label>Status:</label>
          <select v-model="newTask.status" class="form-control">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div class="form-group">
          <label>Due Date:</label>
          <input v-model="newTask.due_date" type="date" class="form-control" />
        </div>
        <button type="submit" class="btn btn-success" :disabled="loading">
          {{ loading ? 'Adding...' : 'Add Task' }}
        </button>
      </form>
    </div>

    <!-- Edit Task Form -->
    <div v-if="editingTask" class="form-container">
      <h3>Edit Task</h3>
      <form @submit.prevent="updateTask">
        <div class="form-group">
          <label>Title: <span class="required">*</span></label>
          <input v-model="editingTask.title" type="text" required maxlength="255" class="form-control" />
        </div>
        <div class="form-group">
          <label>Description:</label>
          <textarea v-model="editingTask.description" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label>Status:</label>
          <select v-model="editingTask.status" class="form-control">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div class="form-group">
          <label>Due Date:</label>
          <input v-model="editingTask.due_date" type="date" class="form-control" />
        </div>
        <button type="submit" class="btn btn-success" :disabled="loading">
          {{ loading ? 'Updating...' : 'Update Task' }}
        </button>
        <button type="button" @click="cancelEdit" class="btn btn-secondary">Cancel</button>
      </form>
    </div>

    <!-- Loading State -->
    <div v-if="loading && tasks.length === 0" class="loading">
      Loading tasks...
    </div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Pagination Info (Bonus Feature) -->
    <div v-if="pagination && tasks.length > 0" class="pagination-info">
      <p>Showing {{ tasks.length }} of {{ pagination.total }} tasks (Page {{ pagination.current_page }} of {{ pagination.last_page }})</p>
      <div class="pagination-controls">
        <button @click="loadTasks(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn btn-sm">
          Previous
        </button>
        <button @click="loadTasks(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn btn-sm">
          Next
        </button>
      </div>
    </div>

    <!-- Tasks List -->
    <div v-if="tasks.length > 0" class="tasks-grid">
      <div v-for="task in tasks" :key="task.id" class="task-card" :class="`status-${task.status}`">
        <div class="task-info">
          <h3>{{ task.title }}</h3>
          <p v-if="task.description" class="task-description">{{ task.description }}</p>
          <div class="task-meta">
            <span class="status-badge" :class="`status-${task.status}`">
              {{ formatStatus(task.status) }}
            </span>
            <span v-if="task.due_date" class="due-date">
              Due: {{ formatDate(task.due_date) }}
            </span>
          </div>
        </div>
        <div class="task-actions">
          <button @click="editTask(task)" class="btn btn-edit">Edit</button>
          <button @click="updateTaskStatus(task)" class="btn btn-status">
            {{ task.status === 'completed' ? 'Mark Incomplete' : 'Mark Complete' }}
          </button>
          <button @click="deleteTask(task.id)" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && tasks.length === 0" class="empty-state">
      <p>No tasks found. Create your first task!</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api/v1';

export default {
  name: 'Tasks',
  data() {
    return {
      tasks: [],
      loading: false,
      error: null,
      showAddForm: false,
      editingTask: null,
      statusFilter: '',
      pagination: null,
      newTask: {
        title: '',
        description: '',
        status: 'pending',
        due_date: '',
      },
    };
  },
  mounted() {
    this.loadTasks();
  },
  methods: {
    async loadTasks(page = 1) {
      this.loading = true;
      this.error = null;
      
      try {
        const params = new URLSearchParams();
        if (this.statusFilter) {
          params.append('status', this.statusFilter);
        }
        params.append('per_page', '10');
        params.append('page', page);

        const response = await axios.get(`${API_URL}/tasks?${params.toString()}`);
        
        if (response.data.meta) {
          // Paginated response
          this.tasks = response.data.data;
          this.pagination = response.data.meta;
        } else {
          // Non-paginated response
          this.tasks = response.data.data || response.data;
          this.pagination = null;
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load tasks';
        console.error('Error loading tasks:', error);
      } finally {
        this.loading = false;
      }
    },
    async addTask() {
      this.loading = true;
      this.error = null;

      try {
        await axios.post(`${API_URL}/tasks`, this.newTask);
        this.newTask = {
          title: '',
          description: '',
          status: 'pending',
          due_date: '',
        };
        this.showAddForm = false;
        await this.loadTasks();
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create task';
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat();
          this.error = errors.join(', ');
        }
      } finally {
        this.loading = false;
      }
    },
    editTask(task) {
      this.editingTask = { ...task };
      this.showAddForm = false;
    },
    cancelEdit() {
      this.editingTask = null;
    },
    async updateTask() {
      if (!this.editingTask) return;

      this.loading = true;
      this.error = null;

      try {
        await axios.put(`${API_URL}/tasks/${this.editingTask.id}`, {
          title: this.editingTask.title,
          description: this.editingTask.description,
          status: this.editingTask.status,
          due_date: this.editingTask.due_date,
        });
        this.editingTask = null;
        await this.loadTasks();
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update task';
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat();
          this.error = errors.join(', ');
        }
      } finally {
        this.loading = false;
      }
    },
    async updateTaskStatus(task) {
      this.loading = true;
      this.error = null;

      try {
        const newStatus = task.status === 'completed' ? 'pending' : 'completed';
        await axios.put(`${API_URL}/tasks/${task.id}`, {
          status: newStatus,
        });
        await this.loadTasks();
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update task status';
      } finally {
        this.loading = false;
      }
    },
    async deleteTask(id) {
      if (!confirm('Are you sure you want to delete this task?')) {
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        await axios.delete(`${API_URL}/tasks/${id}`);
        await this.loadTasks();
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete task';
      } finally {
        this.loading = false;
      }
    },
    formatStatus(status) {
      return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
  },
};
</script>

<style scoped>
.tasks-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filter-section {
  margin-bottom: 20px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.filter-section label {
  margin-right: 10px;
  font-weight: 600;
}

.filter-select {
  display: inline-block;
  width: auto;
  min-width: 200px;
}

.form-container {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
}

.required {
  color: #dc3545;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.tasks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.task-card {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.task-card.status-pending {
  border-left: 4px solid #ffc107;
}

.task-card.status-in_progress {
  border-left: 4px solid #007bff;
}

.task-card.status-completed {
  border-left: 4px solid #28a745;
  opacity: 0.8;
}

.task-info h3 {
  margin: 0 0 10px 0;
  color: #333;
}

.task-description {
  color: #666;
  margin-bottom: 15px;
  line-height: 1.5;
}

.task-meta {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 15px;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-badge.status-in_progress {
  background: #cfe2ff;
  color: #084298;
}

.status-badge.status-completed {
  background: #d1e7dd;
  color: #0f5132;
}

.due-date {
  color: #666;
  font-size: 14px;
}

.task-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background: #218838;
}

.btn-edit {
  background: #ffc107;
  color: #000;
}

.btn-edit:hover:not(:disabled) {
  background: #e0a800;
}

.btn-status {
  background: #17a2b8;
  color: white;
}

.btn-status:hover:not(:disabled) {
  background: #138496;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #c82333;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #5a6268;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.loading, .error-message, .empty-state {
  text-align: center;
  padding: 40px;
  color: #666;
}

.error-message {
  color: #dc3545;
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 4px;
  padding: 15px;
  margin-bottom: 20px;
}

.pagination-info {
  margin: 20px 0;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination-controls {
  display: flex;
  gap: 10px;
}
</style>
