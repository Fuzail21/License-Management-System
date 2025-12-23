#!/bin/bash

# Azure App Service Deployment Script for Laravel
# This script runs during deployment

echo "🚀 Starting Laravel deployment..."

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install npm dependencies and build assets
echo "📦 Installing npm dependencies..."
npm ci --production

echo "🔨 Building production assets..."
npm run build

# Clear and cache configuration
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Create storage link
echo "🔗 Creating storage symlink..."
php artisan storage:link || true

# Set permissions (if needed)
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache || true
chmod -R 775 storage/framework storage/logs || true

echo "✅ Deployment completed successfully!"

