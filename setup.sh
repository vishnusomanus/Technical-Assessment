#!/bin/bash

echo "🚀 Setting up Laravel + Vue.js Application..."

# Note: .env file will be created automatically by docker-entrypoint.sh
# This script is optional and mainly for local development

echo ""
echo "✅ Setup script complete!"
echo ""
echo "The project is configured for automatic initialization."
echo ""
echo "To start the application:"
echo "1. Run: docker-compose up -d --build"
echo ""
echo "The backend will automatically:"
echo "- Wait for MySQL to be ready"
echo "- Create .env file if needed"
echo "- Install Composer dependencies"
echo "- Generate application key"
echo "- Run database migrations"
echo ""
echo "Then access:"
echo "- Frontend: http://localhost:3000"
echo "- Backend API: http://localhost:8080/api/v1/users"
echo ""
echo "For manual setup (if needed):"
echo "1. docker-compose exec backend composer install"
echo "2. docker-compose exec backend php artisan key:generate"
echo "3. docker-compose exec backend php artisan migrate"
echo "4. docker-compose exec frontend npm install"
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8080

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=laravel

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"
EOF
    echo "✅ Backend .env file created"
else
    echo "ℹ️  Backend .env file already exists"
fi

# Create .env file for frontend if it doesn't exist
if [ ! -f frontend/.env ]; then
    echo "📝 Creating frontend/.env file..."
    echo "VITE_API_URL=http://localhost:8080/api/v1" > frontend/.env
    echo "✅ Frontend .env file created"
else
    echo "ℹ️  Frontend .env file already exists"
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "Next steps:"
echo "1. Run: docker-compose up -d --build"
echo "2. Run: docker-compose exec backend composer install"
echo "3. Run: docker-compose exec backend php artisan key:generate"
echo "4. Run: docker-compose exec backend php artisan migrate"
echo "5. Run: docker-compose exec backend php artisan db:seed (optional)"
echo "6. Run: docker-compose exec frontend npm install"
echo ""
echo "Then access:"
echo "- Frontend: http://localhost:3000"
echo "- Backend API: http://localhost:8080/api/v1/users"

