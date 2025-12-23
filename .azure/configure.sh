#!/bin/bash

# Azure App Service Configuration Script
# This ensures Laravel is properly configured

echo "🔧 Configuring Laravel for Azure App Service..."

# Ensure we're in the right directory
cd /home/site/wwwroot

# Create .env from environment variables if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from environment variables..."
    touch .env
    
    # Add basic Laravel config
    echo "APP_NAME=\"${APP_NAME:-License Management System}\"" >> .env
    echo "APP_ENV=${APP_ENV:-production}" >> .env
    echo "APP_KEY=${APP_KEY}" >> .env
    echo "APP_DEBUG=${APP_DEBUG:-false}" >> .env
    echo "APP_URL=${APP_URL}" >> .env
    
    # Database config
    echo "DB_CONNECTION=${DB_CONNECTION:-sqlsrv}" >> .env
    echo "DB_HOST=${DB_HOST}" >> .env
    echo "DB_PORT=${DB_PORT:-1433}" >> .env
    echo "DB_DATABASE=${DB_DATABASE}" >> .env
    echo "DB_USERNAME=${DB_USERNAME}" >> .env
    echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
    echo "DB_CHARSET=${DB_CHARSET:-utf8}" >> .env
    echo "DB_ENCRYPT=${DB_ENCRYPT:-yes}" >> .env
    echo "DB_TRUST_SERVER_CERTIFICATE=${DB_TRUST_SERVER_CERTIFICATE:-false}" >> .env
fi

# Ensure storage directories exist and have correct permissions
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/framework storage/logs

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Clear and cache config
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Cache for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "✅ Configuration completed!"

