# Fix Azure App Service "Waiting for Content" Issue

## Problem
Azure App Service shows "Your web app is running and waiting for your content" even after deployment.

## Solution Steps

### Step 1: Set Document Root to `/public`

**For Linux App Service:**
1. Go to Azure Portal → Your App Service
2. Navigate to **Configuration** → **General settings**
3. Find **Startup Command** and set it to:
   ```bash
   cp -r /home/site/wwwroot/public/. /home/site/wwwroot/ && cp -r /home/site/wwwroot/* /home/site/wwwroot/public/ 2>/dev/null || true && apache2-foreground
   ```
   OR simply leave it empty and set **Path mappings** instead:
   - **Virtual path**: `/`
   - **Physical path**: `/home/site/wwwroot/public`

**For Windows App Service:**
1. Go to **Configuration** → **Path mappings**
2. Add a new virtual application:
   - **Virtual path**: `/`
   - **Physical path**: `site\wwwroot\public`

### Step 2: Verify Deployment Center Settings

1. Go to **Deployment Center**
2. Check **Build Provider**:
   - For **Linux**: Should be "GitHub Actions" or "App Service build service"
   - For **Windows**: Should be "App Service build service"
3. Verify **Repository** and **Branch** are correct

### Step 3: Check Deployment Logs

1. Go to **Deployment Center** → **Logs**
2. Check if deployment completed successfully
3. Look for any errors in the build process

### Step 4: Verify Files Are Deployed

1. Go to **Advanced Tools (Kudu)** → **Go**
2. Navigate to **Debug console** → **CMD** (or **Bash** for Linux)
3. Check if files exist in `/home/site/wwwroot/` (Linux) or `D:\home\site\wwwroot\` (Windows)
4. Verify `public/index.php` exists

### Step 5: Enable Deployment Script

**For Linux:**
1. Go to **Configuration** → **General settings**
2. Set **SCM_DO_BUILD_DURING_DEPLOYMENT** to `true` (if not already set)

**For Windows:**
1. Go to **Configuration** → **Application settings**
2. Add/verify:
   - `SCM_DO_BUILD_DURING_DEPLOYMENT` = `true`
   - `ENABLE_ORYX_BUILD` = `true`

### Step 6: Manual Deployment Test

If automatic deployment isn't working, try manual deployment:

**Using Azure CLI:**
```bash
az webapp deployment source config-zip \
  --resource-group your-resource-group \
  --name your-app-name \
  --src deploy.zip
```

**Or use Kudu:**
1. Go to **Advanced Tools (Kudu)** → **Go**
2. Navigate to **Tools** → **Zip Push Deploy**
3. Upload a zip of your project

### Step 7: Check Application Logs

1. Go to **Log stream** in Azure Portal
2. Check for PHP errors or Laravel errors
3. Also check **Logs** → **Application Logging**

### Step 8: Verify Environment Variables

1. Go to **Configuration** → **Application settings**
2. Verify all required variables are set:
   - `APP_KEY` (must be set!)
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - Database credentials
   - etc.

### Step 9: Restart App Service

After making changes:
1. Go to **Overview**
2. Click **Restart**
3. Wait a few minutes and check again

## Quick Fix Commands (via SSH/Kudu)

**Linux (Bash):**
```bash
cd /home/site/wwwroot
ls -la
ls -la public/
php artisan --version
```

**Windows (CMD):**
```cmd
cd D:\home\site\wwwroot
dir
dir public
php artisan --version
```

## Common Issues and Solutions

### Issue 1: Files not in wwwroot
**Solution:** Check deployment logs, ensure build completes

### Issue 2: Document root wrong
**Solution:** Set path mapping to `/public` directory

### Issue 3: Missing APP_KEY
**Solution:** Generate and set in Application settings:
```bash
php artisan key:generate --show
```

### Issue 4: Permissions issue
**Solution:** Set proper permissions:
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/framework storage/logs
```

### Issue 5: SQL Server extensions not loaded
**Solution:** Add startup command or use custom Docker image

## Alternative: Use Deployment Slot

1. Create a **Deployment Slot** (staging)
2. Deploy to staging first
3. Test staging environment
4. Swap to production when ready

## Still Not Working?

1. **Check GitHub Actions logs** (if using GitHub Actions)
2. **Check Kudu logs**: `/home/LogFiles/` (Linux) or `D:\home\LogFiles\` (Windows)
3. **Enable detailed error pages**: Set `APP_DEBUG=true` temporarily (remember to change back!)
4. **Check PHP version**: Should be 8.2
5. **Verify Composer and npm are available** in the build environment

