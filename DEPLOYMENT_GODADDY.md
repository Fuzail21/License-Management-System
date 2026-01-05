# Deploying Laravel Application to GoDaddy Shared Hosting

## ⚠️ Important Considerations

Your application requires:
- **PHP 8.2+** (Laravel 12.0 requirement)
- **MySQL Database**
- **Composer** (for dependencies)
- **Node.js/npm** (for building assets - can be done locally)

## Prerequisites Check

Before deploying, verify with GoDaddy:
1. ✅ PHP version 8.2 or higher is available
2. ✅ Composer is available via SSH or cPanel
3. ✅ MySQL database is available
4. ✅ SSH access (recommended) or File Manager access
5. ✅ Ability to modify `.htaccess` files

## Deployment Steps

### Step 1: Prepare Your Application Locally

1. **Build production assets:**
   ```bash
   npm run build
   ```

2. **Optimize for production:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Create a deployment package** (exclude unnecessary files):
   - Include: `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`, `artisan`, `composer.json`, `composer.lock`, `.env.example`
   - Exclude: `node_modules/`, `.git/`, `tests/`, `.env`, `storage/logs/*`, `storage/framework/cache/*`, `storage/framework/sessions/*`, `storage/framework/views/*`

### Step 2: Upload Files to GoDaddy

**Option A: Using FTP/SFTP**
1. Connect to your GoDaddy hosting via FTP
2. Upload all files to `public_html/` (or your domain's root directory)
3. **Important:** Move contents of `public/` folder to `public_html/` root
4. Move all other Laravel files one level up (outside public_html)

**Option B: Using cPanel File Manager**
1. Log into cPanel
2. Use File Manager to upload and organize files

**Recommended File Structure on GoDaddy:**
```
/home/username/
├── public_html/          (Document Root - contains only public files)
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   └── storage/ (symlink)
├── laravel_app/          (Laravel application files)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   └── composer.json
└── .env
```

### Step 3: Configure .htaccess

Create/update `public_html/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Step 4: Update public/index.php

If Laravel files are outside `public_html`, update `public_html/index.php`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../laravel_app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../laravel_app/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

### Step 5: Set Up Environment File

1. Copy `.env.example` to `.env` in your Laravel root directory
2. Update `.env` with your GoDaddy database credentials:

```env
APP_NAME="License Management System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_godaddy_db_name
DB_USERNAME=your_godaddy_db_user
DB_PASSWORD=your_godaddy_db_password

# ... other settings
```

3. Generate application key (via SSH or cPanel Terminal):
   ```bash
   php artisan key:generate
   ```

### Step 6: Install Dependencies (if not uploaded)

If you didn't upload `vendor/` folder, install via SSH:
```bash
cd ~/laravel_app
composer install --no-dev --optimize-autoloader
```

### Step 7: Set Permissions

Set proper permissions (via SSH or cPanel):
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/framework
chmod -R 775 storage/logs
```

### Step 8: Run Migrations

Via SSH or cPanel Terminal:
```bash
cd ~/laravel_app
php artisan migrate --force
```

### Step 9: Create Storage Link

```bash
php artisan storage:link
```

If symlinks don't work on GoDaddy, you may need to manually copy or use a different approach for file storage.

### Step 10: Clear and Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Alternative: Simplified Deployment (All in public_html)

If GoDaddy doesn't allow files outside `public_html`:

1. Upload all Laravel files to `public_html/`
2. Move `public/` contents to `public_html/` root
3. Update `public_html/index.php` to point to correct paths
4. Update `.htaccess` to protect sensitive files

## Troubleshooting

### Common Issues:

1. **500 Internal Server Error**
   - Check `.env` file exists and is configured
   - Check file permissions
   - Check error logs in `storage/logs/`

2. **Composer not found**
   - Use cPanel's Composer tool if available
   - Or upload pre-built `vendor/` folder

3. **Database Connection Error**
   - Verify database credentials in `.env`
   - Check if database host is `localhost` or specific GoDaddy host

4. **Storage/Symlink Issues**
   - GoDaddy may not support symlinks
   - Consider using absolute paths or different storage configuration

5. **PHP Version**
   - Ensure PHP 8.2+ is selected in cPanel
   - May need to create `.htaccess` or `php.ini` to set version

## Security Recommendations

1. ✅ Set `APP_DEBUG=false` in production
2. ✅ Use strong `APP_KEY`
3. ✅ Protect `.env` file (should not be web-accessible)
4. ✅ Keep Laravel files outside public directory if possible
5. ✅ Regularly update dependencies

## Performance Tips

1. Enable Laravel's caching (config, routes, views)
2. Use CDN for static assets if possible
3. Optimize database queries
4. Consider using GoDaddy's caching features

## Need Help?

If GoDaddy shared hosting is too limiting, consider:
- **GoDaddy VPS/Managed WordPress** (better Laravel support)
- **DigitalOcean Droplet** (full control)
- **Laravel Forge** (automated deployment)
- **Shared hosting alternatives** (A2 Hosting, SiteGround - better Laravel support)

