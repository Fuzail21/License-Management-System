# Quick Fix for Azure App Service "Waiting for Content"

## Immediate Fix (Do This First!)

### Step 1: Set Document Root in Azure Portal

1. **Go to Azure Portal** → Your App Service: `license-management-system-euhjepazhygcezc6`
2. Navigate to **Configuration** → **General settings**
3. **For Linux App Service:**
   - Find **Path mappings** section
   - Click **+ New path mapping**
   - **Virtual path**: `/`
   - **Physical path**: `/home/site/wwwroot/public`
   - Click **OK** → **Save**

4. **For Windows App Service:**
   - Find **Virtual applications and directories**
   - Change the root `/` path to: `site\wwwroot\public`
   - Click **Save**

### Step 2: Verify APP_KEY is Set

1. Go to **Configuration** → **Application settings**
2. Check if `APP_KEY` exists
3. If not, generate one:
   ```bash
   # Run locally or in Kudu console
   php artisan key:generate --show
   ```
4. Add it to Application settings as `APP_KEY`

### Step 3: Restart App Service

1. Go to **Overview**
2. Click **Restart**
3. Wait 2-3 minutes

### Step 4: Check Deployment Status

1. Go to **Deployment Center** → **Logs**
2. Verify the last deployment was successful
3. If it failed, check the error messages

## If Still Not Working

### Check Files via Kudu

1. Go to **Advanced Tools (Kudu)** → **Go**
2. Click **Debug console** → **CMD** (or **Bash**)
3. Navigate to: `cd site\wwwroot` (Windows) or `cd /home/site/wwwroot` (Linux)
4. Check if these files exist:
   - `public/index.php` ✅
   - `artisan` ✅
   - `composer.json` ✅
   - `vendor/` directory ✅

### Manual File Check Commands

**Windows (Kudu CMD):**
```cmd
cd D:\home\site\wwwroot
dir
dir public
dir public\index.php
```

**Linux (Kudu Bash):**
```bash
cd /home/site/wwwroot
ls -la
ls -la public/
ls -la public/index.php
```

### Force Redeploy

1. Go to **Deployment Center**
2. Click **Sync** or **Redeploy**
3. Or make a small commit and push to trigger deployment:
   ```bash
   git commit --allow-empty -m "Trigger deployment"
   git push origin main
   ```

## Common Solutions

### Solution 1: Document Root Not Set
**Fix:** Set path mapping to `/public` (see Step 1 above)

### Solution 2: Files Not Deployed
**Fix:** Check deployment logs, ensure GitHub Actions completed successfully

### Solution 3: Missing APP_KEY
**Fix:** Generate and add to Application settings

### Solution 4: Wrong Startup Command
**Fix:** Leave startup command empty or use:
```bash
apache2-foreground
```

### Solution 5: Build Failed
**Fix:** Check GitHub Actions logs, verify all dependencies are in composer.json

## Verify It's Working

After applying fixes, check:
1. Visit: `https://license-management-system-euhjepazhygcezc6.canadacentral-01.azurewebsites.net`
2. Should see Laravel login page (not "waiting for content")
3. Check **Log stream** for any errors

## Still Having Issues?

1. **Enable Application Logging:**
   - Configuration → Application settings
   - Add: `APP_DEBUG=true` (temporarily)
   - Check **Log stream** for detailed errors

2. **Check PHP Version:**
   - Configuration → General settings
   - PHP version should be 8.2

3. **Verify Environment Variables:**
   - All DB_* variables are set correctly
   - APP_KEY is set
   - APP_URL matches your app URL

