#!/bin/bash

# Script to switch between local and production environments
# Usage: ./switch-env.sh [local|production]

ENV=${1:-local}

if [ "$ENV" = "local" ]; then
    echo "Switching to LOCAL environment..."
    
    # Copy local environment files
    if [ -f ".env.local" ]; then
        cp .env.local .env
        echo "✓ Copied .env.local to .env"
    else
        echo "⚠ .env.local not found"
    fi
    
    if [ -f "Post_Generation/.env.local" ]; then
        cp Post_Generation/.env.local Post_Generation/.env
        echo "✓ Copied Post_Generation/.env.local to Post_Generation/.env"
    else
        echo "⚠ Post_Generation/.env.local not found"
    fi
    
    echo "✅ Switched to LOCAL environment"
    echo "Domain: http://localhost:8000"
    
elif [ "$ENV" = "production" ]; then
    echo "Switching to PRODUCTION environment..."
    
    # Copy production environment files
    if [ -f ".env.production" ]; then
        cp .env.production .env
        echo "✓ Copied .env.production to .env"
    else
        echo "⚠ .env.production not found"
    fi
    
    if [ -f "Post_Generation/.env.production" ]; then
        cp Post_Generation/.env.production Post_Generation/.env
        echo "✓ Copied Post_Generation/.env.production to Post_Generation/.env"
    else
        echo "⚠ Post_Generation/.env.production not found"
    fi
    
    echo "✅ Switched to PRODUCTION environment"
    echo "Domain: https://socialmidsai.com"
    
else
    echo "❌ Invalid environment. Use 'local' or 'production'"
    echo "Usage: ./switch-env.sh [local|production]"
    exit 1
fi

echo ""
echo "Current environment variables:"
echo "APP_DOMAIN: $(grep APP_DOMAIN .env 2>/dev/null | cut -d'=' -f2 || echo 'Not set')"
echo "VITE_APP_DOMAIN: $(grep VITE_APP_DOMAIN .env 2>/dev/null | cut -d'=' -f2 || echo 'Not set')" 