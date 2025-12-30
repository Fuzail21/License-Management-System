#!/bin/bash

# Azure App Service startup script for Laravel
# This script configures the document root to point to /public

echo "Starting Laravel application setup..."

# Navigate to application directory
cd /home/site/wwwroot

# Set permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage/framework storage/logs 2>/dev/null || true

# Create symbolic links from public to root
echo "Creating symbolic links..."
if [ -d "public" ]; then
    # Copy .htaccess to root if it doesn't exist
    if [ ! -f "/home/site/wwwroot/.htaccess" ]; then
        cp public/.htaccess .htaccess 2>/dev/null || true
    fi

    # Create symbolic link for storage
    if [ ! -L "public/storage" ]; then
        php artisan storage:link 2>/dev/null || true
    fi
fi

# Configure Apache to use public directory as DocumentRoot
echo "Configuring Apache DocumentRoot..."
cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:8080>
    ServerAdmin webmaster@localhost
    DocumentRoot /home/site/wwwroot/public

    <Directory /home/site/wwwroot/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Reload Apache configuration
echo "Reloading Apache..."
service apache2 reload 2>/dev/null || true

echo "Laravel application setup complete!"

# Start Apache in foreground
apache2-foreground
