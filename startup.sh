#!/bin/bash

echo "Starting Laravel application on Azure App Service with nginx..."

# Set working directory
cd /home/site/wwwroot

# Copy custom nginx configuration
if [ -f "/home/site/wwwroot/nginx_default.conf" ]; then
    echo "Copying custom nginx configuration..."
    cp /home/site/wwwroot/nginx_default.conf /etc/nginx/sites-available/default
    nginx -t && nginx -s reload 2>/dev/null || true
    echo "Nginx configuration updated!"
fi

# Set proper permissions for Laravel
echo "Setting permissions..."
chmod -R 755 /home/site/wwwroot
chmod -R 775 /home/site/wwwroot/storage 2>/dev/null || true
chmod -R 775 /home/site/wwwroot/bootstrap/cache 2>/dev/null || true

# Create storage link if it doesn't exist
if [ ! -L "/home/site/wwwroot/public/storage" ]; then
    echo "Creating storage symlink..."
    php artisan storage:link 2>/dev/null || true
fi

echo "Laravel application setup complete!"
