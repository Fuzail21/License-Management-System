#!/bin/bash
# Environment Switcher for License Management System
# Usage: ./switch-env.sh [local|production]

if [ -z "$1" ]; then
    echo ""
    echo "============================================"
    echo "  Environment Switcher"
    echo "============================================"
    echo ""
    echo "Usage: ./switch-env.sh [local|production]"
    echo ""
    echo "Examples:"
    echo "  ./switch-env.sh local       - Switch to local XAMPP environment"
    echo "  ./switch-env.sh production  - Switch to Azure production environment"
    echo ""
    exit 1
fi

case "$1" in
    local)
        echo ""
        echo "Switching to LOCAL environment..."
        echo ""

        if [ ! -f .env.local ]; then
            echo "ERROR: .env.local file not found!"
            echo "Please create .env.local file first."
            exit 1
        fi

        cp -f .env.local .env
        echo "[OK] Environment file copied: .env.local -> .env"

        echo "[*] Clearing configuration cache..."
        php artisan config:clear > /dev/null 2>&1

        echo "[*] Clearing route cache..."
        php artisan route:clear > /dev/null 2>&1

        echo "[*] Clearing view cache..."
        php artisan view:clear > /dev/null 2>&1

        echo ""
        echo "============================================"
        echo "  LOCAL Environment Active"
        echo "============================================"
        echo ""
        echo "Database: license_mangement_system (SQL Server 127.0.0.1)"
        echo "App URL:  http://localhost"
        echo "Debug:    Enabled"
        echo ""
        echo "You can now run:"
        echo "  - php artisan serve"
        echo "  - php artisan migrate"
        echo "  - npm run dev"
        echo ""
        ;;

    production)
        echo ""
        echo "Switching to PRODUCTION environment..."
        echo ""

        if [ ! -f .env.production ]; then
            echo "ERROR: .env.production file not found!"
            echo "Please create .env.production file first."
            exit 1
        fi

        cp -f .env.production .env
        echo "[OK] Environment file copied: .env.production -> .env"

        echo "[*] Clearing configuration cache..."
        php artisan config:clear > /dev/null 2>&1

        echo "[*] Clearing route cache..."
        php artisan route:clear > /dev/null 2>&1

        echo "[*] Clearing view cache..."
        php artisan view:clear > /dev/null 2>&1

        echo ""
        echo "============================================"
        echo "  PRODUCTION Environment Active"
        echo "============================================"
        echo ""
        echo "Database: Azure SQL Database"
        echo "App URL:  https://your-app-name.azurewebsites.net"
        echo "Debug:    Disabled"
        echo ""
        echo "IMPORTANT: Update .env.production with your Azure credentials!"
        echo ""
        echo "You can now run:"
        echo "  - git add ."
        echo "  - git commit -m \"Your commit message\""
        echo "  - git push azure main"
        echo ""
        ;;

    *)
        echo ""
        echo "ERROR: Invalid environment \"$1\""
        echo ""
        echo "Valid options: local, production"
        echo ""
        exit 1
        ;;
esac
