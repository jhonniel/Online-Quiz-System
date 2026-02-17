#!/bin/bash

# Online Quiz System - Server Startup Script
echo "🚀 Starting Online Quiz System..."

# Set PHP memory limit
export PHP_MEMORY_LIMIT=512M

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Start server with proper memory limit
echo "🌐 Starting Laravel development server..."
php -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8000

echo "✅ Server started successfully!"
echo "📱 Access your system at: http://localhost:8000"
echo "🌍 Network access: http://YOUR_IP:8000"
