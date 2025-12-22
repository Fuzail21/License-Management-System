<?php
/**
 * GoDaddy Shared Hosting Deployment Preparation Script
 * 
 * Run this script locally before uploading to GoDaddy
 * Usage: php prepare-godaddy-deployment.php
 */

echo "🚀 Preparing Laravel application for GoDaddy deployment...\n\n";

// Step 1: Check if we're in Laravel root
if (!file_exists('artisan')) {
    die("❌ Error: Please run this script from the Laravel root directory.\n");
}

echo "✅ Laravel root directory detected.\n";

// Step 2: Build assets
echo "\n📦 Building production assets...\n";
exec('npm run build', $output, $return);
if ($return !== 0) {
    echo "⚠️  Warning: npm build may have failed. Make sure assets are built.\n";
} else {
    echo "✅ Assets built successfully.\n";
}

// Step 3: Optimize for production
echo "\n⚡ Optimizing for production...\n";

$commands = [
    'php artisan config:cache',
    'php artisan route:cache',
    'php artisan view:cache',
];

foreach ($commands as $command) {
    exec($command, $output, $return);
    if ($return === 0) {
        echo "✅ " . explode(' ', $command)[2] . " cached.\n";
    } else {
        echo "⚠️  Warning: " . explode(' ', $command)[2] . " caching may have failed.\n";
    }
}

// Step 4: Create deployment checklist
echo "\n📋 Creating deployment checklist...\n";

$checklist = <<<'CHECKLIST'
# GoDaddy Deployment Checklist

## Before Upload:
- [ ] Run: npm run build
- [ ] Run: php artisan config:cache
- [ ] Run: php artisan route:cache
- [ ] Run: php artisan view:cache
- [ ] Verify .env.example exists
- [ ] Remove .env file (don't upload it)
- [ ] Remove node_modules/ folder
- [ ] Remove .git/ folder (optional)

## On GoDaddy:
- [ ] Upload all files to server
- [ ] Move public/ contents to public_html/
- [ ] Move other Laravel files outside public_html (if possible)
- [ ] Create .env file with production settings
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Configure database credentials
- [ ] Run: php artisan key:generate
- [ ] Run: php artisan migrate --force
- [ ] Run: php artisan storage:link
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Set storage/ permissions to 775
- [ ] Verify .htaccess is in public_html/
- [ ] Test the application

## File Permissions:
- storage/ and storage/* should be 775
- bootstrap/cache/ should be 775
- All other directories: 755
- All files: 644

## Important Files to Upload:
✅ app/
✅ bootstrap/
✅ config/
✅ database/
✅ public/ (contents to public_html/)
✅ resources/
✅ routes/
✅ storage/ (empty framework folders)
✅ vendor/
✅ artisan
✅ composer.json
✅ composer.lock
✅ .env.example

## Files to Exclude:
❌ .env
❌ node_modules/
❌ .git/
❌ tests/
❌ storage/logs/*.log
❌ storage/framework/cache/*
❌ storage/framework/sessions/*
❌ storage/framework/views/*

CHECKLIST;

file_put_contents('GODADDY_CHECKLIST.md', $checklist);
echo "✅ Checklist created: GODADDY_CHECKLIST.md\n";

// Step 5: Create .htaccess for public_html
echo "\n📝 Creating .htaccess template...\n";

$htaccess = <<<'HTACCESS'
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

# Protect sensitive files
<FilesMatch "^(\.env|\.git|composer\.(json|lock)|package(-lock)?\.json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

HTACCESS;

file_put_contents('public_html_htaccess.txt', $htaccess);
echo "✅ .htaccess template created: public_html_htaccess.txt\n";

echo "\n✨ Preparation complete!\n";
echo "\n📖 Next steps:\n";
echo "1. Review GODADDY_CHECKLIST.md\n";
echo "2. Upload files to GoDaddy\n";
echo "3. Follow the deployment guide in DEPLOYMENT_GODADDY.md\n";
echo "4. Copy public_html_htaccess.txt content to public_html/.htaccess\n\n";

