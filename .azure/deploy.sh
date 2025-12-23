#!/bin/bash

# Azure App Service Deployment Script for Laravel
# This script runs during deployment

echo "🚀 Starting Laravel deployment..."

# Change to deployment directory
cd /home/site/wwwroot || exit 1

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
if [ -f composer.json ]; then
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
else
    echo "⚠️ composer.json not found!"
    exit 1
fi

# Install npm dependencies and build assets
echo "📦 Installing npm dependencies..."
if [ -f package.json ]; then
    npm ci --production --legacy-peer-deps || npm install --production --legacy-peer-deps
    
    echo "🔨 Building production assets..."
    npm run build || echo "⚠️ Build failed, continuing..."
else
    echo "⚠️ package.json not found, skipping npm build"
fi

# Ensure storage directories exist
echo "📁 Creating storage directories..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/framework storage/logs

# Create storage link
echo "🔗 Creating storage symlink..."
php artisan storage:link || echo "Storage link already exists or failed"

# Clear caches before caching
echo "🧹 Clearing old caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# Cache for production
echo "⚡ Optimizing Laravel..."
php artisan config:cache || echo "Config cache failed"
php artisan route:cache || echo "Route cache failed"
php artisan view:cache || echo "View cache failed"

# Run migrations (only if DB is configured)
echo "🗄️ Running database migrations..."
php artisan migrate --force || echo "⚠️ Migrations failed or skipped"

echo "✅ Deployment completed successfully!"

