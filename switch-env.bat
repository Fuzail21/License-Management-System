@echo off
REM Environment Switcher for License Management System
REM Usage: switch-env.bat [local|production]

IF "%1"=="" (
    echo.
    echo ============================================
    echo   Environment Switcher
    echo ============================================
    echo.
    echo Usage: switch-env.bat [local^|production]
    echo.
    echo Examples:
    echo   switch-env.bat local       - Switch to local XAMPP environment
    echo   switch-env.bat production  - Switch to Azure production environment
    echo.
    goto :end
)

IF /I "%1"=="local" (
    echo.
    echo Switching to LOCAL environment...
    echo.

    IF NOT EXIST .env.local (
        echo ERROR: .env.local file not found!
        echo Please create .env.local file first.
        goto :end
    )

    copy /Y .env.local .env >nul
    echo [OK] Environment file copied: .env.local -^> .env

    echo [*] Clearing configuration cache...
    php artisan config:clear >nul 2>&1

    echo [*] Clearing route cache...
    php artisan route:clear >nul 2>&1

    echo [*] Clearing view cache...
    php artisan view:clear >nul 2>&1

    echo.
    echo ============================================
    echo   LOCAL Environment Active
    echo ============================================
    echo.
    echo Database: license_mangement_system ^(SQL Server 127.0.0.1^)
    echo App URL:  http://localhost
    echo Debug:    Enabled
    echo.
    echo You can now run:
    echo   - php artisan serve
    echo   - php artisan migrate
    echo   - npm run dev
    echo.

    goto :end
)

IF /I "%1"=="production" (
    echo.
    echo Switching to PRODUCTION environment...
    echo.

    IF NOT EXIST .env.production (
        echo ERROR: .env.production file not found!
        echo Please create .env.production file first.
        goto :end
    )

    copy /Y .env.production .env >nul
    echo [OK] Environment file copied: .env.production -^> .env

    echo [*] Clearing configuration cache...
    php artisan config:clear >nul 2>&1

    echo [*] Clearing route cache...
    php artisan route:clear >nul 2>&1

    echo [*] Clearing view cache...
    php artisan view:clear >nul 2>&1

    echo.
    echo ============================================
    echo   PRODUCTION Environment Active
    echo ============================================
    echo.
    echo Database: Azure SQL Database
    echo App URL:  https://your-app-name.azurewebsites.net
    echo Debug:    Disabled
    echo.
    echo IMPORTANT: Update .env.production with your Azure credentials!
    echo.
    echo You can now run:
    echo   - git add .
    echo   - git commit -m "Your commit message"
    echo   - git push azure main
    echo.

    goto :end
)

echo.
echo ERROR: Invalid environment "%1"
echo.
echo Valid options: local, production
echo.

:end
