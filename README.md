# Task Management System

A full-stack task management application built with Laravel (backend) and Vue.js (frontend), containerized with Docker.

**Repository:** [https://github.com/vishnusomanus/Technical-Assessment](https://github.com/vishnusomanus/Technical-Assessment)

## Features

- ✅ Task CRUD operations (Create, Read, Update, Delete)
- ✅ Task filtering by status (pending, in_progress, completed)
- ✅ Pagination (10 items per page)
- ✅ Soft deletes
- ✅ RESTful API with proper validation
- ✅ Modern Vue.js frontend interface
- ✅ Service-Repository pattern implementation
- ✅ Comprehensive test coverage

## Tech Stack

- **Backend:** Laravel 10, PHP 8.1, MySQL 8.0
- **Frontend:** Vue.js 3, Vite
- **Infrastructure:** Docker, Docker Compose, Nginx
- **Architecture:** Service-Repository pattern, PSR-12, SOLID principles

## Quick Start

### Prerequisites

- Docker and Docker Compose installed
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/vishnusomanus/Technical-Assessment.git
   cd Technical-Assessment
   ```

2. **Start the application**
   ```bash
   docker-compose up -d --build
   ```

   This will:
   - Build and start MySQL, Laravel backend, Nginx, and Vue.js frontend
   - Automatically run migrations and seed sample data
   - Set up all necessary configurations

3. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8080/api/v1/tasks
   - API Documentation: See API Endpoints section below

## API Endpoints

All endpoints are prefixed with `/api/v1`

### Tasks

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tasks` | List all tasks (with pagination & filtering) |
| POST | `/tasks` | Create a new task |
| GET | `/tasks/{id}` | Get a single task |
| PUT | `/tasks/{id}` | Update a task |
| DELETE | `/tasks/{id}` | Delete a task (soft delete) |

### Query Parameters

- `status` - Filter by status: `pending`, `in_progress`, or `completed`
- `per_page` - Items per page (default: 10)
- `page` - Page number for pagination

### Example Requests

**Create a task:**
```bash
curl -X POST http://localhost:8080/api/v1/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Complete project",
    "description": "Finish the task management system",
    "status": "in_progress",
    "due_date": "2024-12-31"
  }'
```

**List tasks with filtering:**
```bash
curl "http://localhost:8080/api/v1/tasks?status=pending&per_page=10"
```

**Update a task:**
```bash
curl -X PUT http://localhost:8080/api/v1/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'
```

## Running Tests

```bash
# Run all tests
docker-compose exec backend php artisan test

# Run specific test
docker-compose exec backend php artisan test --filter=test_can_create_task_successfully
```

**Test Coverage:**
- ✅ Create task (success case)
- ✅ Create task (validation failure)
- ✅ List tasks
- ✅ Update task
- ✅ Delete task
- ✅ Filter by status
- ✅ Pagination

## Project Structure

```
assesment/
├── backend/              # Laravel backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/  # API controllers
│   │   │   ├── Requests/         # Form request validation
│   │   │   └── Resources/        # API resources
│   │   ├── Models/               # Eloquent models
│   │   ├── Repositories/         # Repository layer
│   │   └── Services/             # Service layer
│   ├── database/
│   │   ├── migrations/           # Database migrations
│   │   ├── factories/            # Model factories
│   │   └── seeders/              # Database seeders
│   └── tests/                    # Feature tests
├── frontend/             # Vue.js frontend
│   └── src/
│       ├── components/  # Vue components
│       └── main.js      # Application entry point
└── docker-compose.yml    # Docker configuration
```

## Common Commands

### Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f backend
docker-compose logs -f frontend

# Rebuild containers
docker-compose up -d --build

# Access backend container
docker-compose exec backend bash

# Access MySQL
docker-compose exec mysql mysql -ularavel -plaravel laravel
```

### Laravel Commands

```bash
# Run migrations
docker-compose exec backend php artisan migrate

# Seed database
docker-compose exec backend php artisan db:seed

# Clear cache
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear

# Generate application key (if needed)
docker-compose exec backend php artisan key:generate
```

## Database

- **Host:** localhost:3306
- **Database:** laravel
- **Username:** laravel
- **Password:** laravel

## Task Model Fields

- `title` (required, string, max 255)
- `description` (optional, text)
- `status` (enum: pending, in_progress, completed)
- `due_date` (optional, date)
- `created_at`, `updated_at`, `deleted_at` (timestamps)

## Troubleshooting

### Containers not starting
```bash
# Check container status
docker-compose ps

# View logs
docker-compose logs
```

### Database connection issues
```bash
# Ensure MySQL is healthy
docker-compose ps mysql

# Restart services
docker-compose restart
```

### Frontend not connecting to API
- Verify `VITE_API_URL` in `docker-compose.yml` matches your backend URL
- Check browser console for CORS errors
- Ensure backend is running on port 8080

## Development

### Backend Development
- Follow PSR-12 coding standards
- Use Service-Repository pattern for business logic
- Form Requests for validation
- API Resources for response formatting

### Frontend Development
- Vue.js 3 Composition API
- Vite for build tooling
- Axios for API calls

## License

MIT
