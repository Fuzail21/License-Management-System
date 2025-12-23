# Azure App Service CI/CD Setup Guide

This guide will help you set up continuous deployment from GitHub to Azure App Service.

## Prerequisites

1. ✅ Azure account with an App Service created
2. ✅ GitHub repository (already set up)
3. ✅ Azure SQL Database configured (already done)

## Step 1: Create Azure App Service

If you haven't created an App Service yet:

1. **Go to Azure Portal** (https://portal.azure.com)
2. **Create a new App Service:**
   - Click "Create a resource"
   - Search for "Web App"
   - Click "Create"
   - Fill in the details:
     - **Subscription**: Your subscription
     - **Resource Group**: Create new or use existing
     - **Name**: `license-management-app` (or your preferred name)
     - **Publish**: Code
     - **Runtime stack**: PHP 8.2
     - **Operating System**: Linux (recommended) or Windows
     - **Region**: Choose closest to your users
     - **App Service Plan**: Create new or use existing
   - Click "Review + create" then "Create"

## Step 2: Configure App Service Settings

### 2.1 Application Settings (Environment Variables)

1. Go to your App Service in Azure Portal
2. Navigate to **Configuration** → **Application settings**
3. Add the following environment variables:

```
APP_NAME=License Management System
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-app-name.azurewebsites.net

DB_CONNECTION=sqlsrv
DB_HOST=2020sqlwebapp.database.windows.net
DB_PORT=1433
DB_DATABASE=license-management
DB_USERNAME=index
DB_PASSWORD=5920@dm!n
DB_CHARSET=utf8
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=false

LOG_CHANNEL=stack
LOG_LEVEL=error
```

**Important:** 
- Generate `APP_KEY` by running `php artisan key:generate` locally and copy the key
- Replace `your-app-name` with your actual App Service name

### 2.2 Enable SQL Server Extensions

For **Linux App Service**, add to **Startup Command** in Configuration:
```bash
apt-get update && apt-get install -y unixodbc-dev && pecl install sqlsrv pdo_sqlsrv && docker-php-ext-enable sqlsrv pdo_sqlsrv && apache2-foreground
```

Or use a **custom startup script** (see below).

## Step 3: Connect GitHub to Azure App Service

### Option A: Using Azure Portal (Recommended)

1. **Go to your App Service** in Azure Portal
2. Navigate to **Deployment Center**
3. **Source**: Select "GitHub"
4. **Authorize** GitHub if prompted
5. **Organization**: Select your GitHub organization/username
6. **Repository**: `Fuzail21/License-Management-System`
7. **Branch**: `main`
8. **Build Provider**: 
   - For **Linux**: Select "GitHub Actions" (recommended)
   - For **Windows**: Select "App Service build service"
9. Click **Save**

Azure will automatically:
- Create a GitHub Actions workflow (if using GitHub Actions)
- Set up deployment triggers
- Configure build settings

### Option B: Manual GitHub Actions Setup

1. **Get Publish Profile:**
   - Go to App Service → **Get publish profile**
   - Download the `.PublishSettings` file
   - Open it and copy the content

2. **Add GitHub Secret:**
   - Go to your GitHub repository
   - Navigate to **Settings** → **Secrets and variables** → **Actions**
   - Click **New repository secret**
   - Name: `AZURE_WEBAPP_PUBLISH_PROFILE`
   - Value: Paste the content from `.PublishSettings` file
   - Click **Add secret**

3. **Update Workflow File:**
   - Edit `.github/workflows/azure-deploy.yml`
   - Update `AZURE_WEBAPP_NAME` with your App Service name
   - Commit and push

## Step 4: Configure Deployment Script

### For Linux App Service:

1. **Create startup script** (if needed):
   - Go to **Configuration** → **General settings**
   - **Startup Command**: Leave empty (or use custom script)

2. **Deployment script** (`.azure/deploy.sh`) will run automatically

### For Windows App Service:

1. **Deployment script** (`.azure/deploy.cmd`) will run automatically
2. Make sure **SCM_DO_BUILD_DURING_DEPLOYMENT** is set to `true` in App Settings

## Step 5: Configure Azure SQL Firewall

1. **Go to Azure SQL Server**: `2020sqlwebapp`
2. Navigate to **Networking**
3. **Add firewall rule** to allow Azure services:
   - Enable **"Allow Azure services and resources to access this server"**
4. **Add your App Service IP** (if needed):
   - Go to your App Service → **Properties**
   - Note the **Outbound IP addresses**
   - Add these IPs to SQL Server firewall rules

## Step 6: Test Deployment

1. **Make a small change** to your code
2. **Commit and push** to GitHub:
   ```bash
   git add .
   git commit -m "Test deployment"
   git push origin main
   ```
3. **Monitor deployment**:
   - Azure Portal → App Service → **Deployment Center** → **Logs**
   - GitHub → **Actions** tab → View workflow runs

## Step 7: Verify Application

1. **Visit your App Service URL**: `https://your-app-name.azurewebsites.net`
2. **Check logs** if there are issues:
   - Azure Portal → App Service → **Log stream**
   - Or **Logs** → **Application Logging**

## Troubleshooting

### Common Issues:

1. **"could not find driver" error**
   - Ensure SQL Server extensions are installed
   - Check startup command includes extension installation
   - For Linux: Use custom Docker image or startup script

2. **Database connection timeout**
   - Verify firewall rules allow Azure services
   - Check connection string in App Settings
   - Verify SQL Server is accessible

3. **Build fails**
   - Check GitHub Actions logs
   - Verify all dependencies in `composer.json` and `package.json`
   - Ensure Node.js and PHP versions are correct

4. **500 Internal Server Error**
   - Check Application Logs in Azure Portal
   - Verify `.env` variables are set in App Settings
   - Check file permissions (storage/, bootstrap/cache/)

5. **Migrations fail**
   - Verify database credentials
   - Check if migrations table exists
   - Review migration logs

## Custom Docker Image (Optional - for Linux)

If you need more control, create a `Dockerfile`:

```dockerfile
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    unixodbc-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm ci && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Configure Apache
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
```

## Additional Resources

- [Azure App Service Documentation](https://docs.microsoft.com/azure/app-service/)
- [Laravel on Azure](https://docs.microsoft.com/azure/app-service/quickstart-php)
- [GitHub Actions for Azure](https://github.com/azure/webapps-deploy)

## Quick Commands Reference

```bash
# View deployment logs
az webapp log tail --name your-app-name --resource-group your-resource-group

# Restart app service
az webapp restart --name your-app-name --resource-group your-resource-group

# View app settings
az webapp config appsettings list --name your-app-name --resource-group your-resource-group

# Set app setting
az webapp config appsettings set --name your-app-name --resource-group your-resource-group --settings APP_KEY="your-key"
```

