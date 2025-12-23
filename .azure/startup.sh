#!/bin/bash

# Startup script for Azure App Service (Linux)
# This ensures SQL Server extensions are loaded

# Enable SQL Server extensions if not already enabled
if ! php -m | grep -q sqlsrv; then
    echo "Installing SQL Server extensions..."
    apt-get update
    apt-get install -y unixodbc-dev
    pecl install sqlsrv pdo_sqlsrv
    docker-php-ext-enable sqlsrv pdo_sqlsrv
fi

# Start Apache
apache2-foreground

