@if "%SCM_DO_BUILD_DURING_DEPLOYMENT%"=="true" goto build
goto skipbuild
:build

echo "🚀 Starting Laravel deployment..."

REM Install Composer dependencies
echo "📦 Installing Composer dependencies..."
call composer install --no-dev --optimize-autoloader --no-interaction

REM Install npm dependencies and build assets
echo "📦 Installing npm dependencies..."
call npm ci --production

echo "🔨 Building production assets..."
call npm run build

REM Clear and cache configuration
echo "⚡ Optimizing Laravel..."
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache

REM Run migrations
echo "🗄️ Running database migrations..."
call php artisan migrate --force

REM Create storage link
echo "🔗 Creating storage symlink..."
call php artisan storage:link || echo "Storage link already exists"

REM Set permissions (if needed on Windows)
echo "✅ Deployment completed successfully!"

:skipbuild
echo "Skipping build step..."

