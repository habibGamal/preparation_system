#!/bin/sh
set -e  # Exit on any error

echo "🚀 Starting deployment..."

# Get the script's directory
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "📁 Working directory: $SCRIPT_DIR"

# 1. Pull latest code (if git repository exists)
if [ -d ".git" ]; then
    echo "📥 Pulling latest code from repository..."
    git reset --hard
    git pull
    echo "✅ Code updated successfully"
else
    echo "⚠️  No git repository found, skipping git pull"
fi

# 2. Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader
echo "✅ PHP dependencies installed"

# 3. Install frontend dependencies and build
echo "🎨 Installing frontend dependencies..."
npm install
echo "🔨 Building frontend assets..."
npm run build
echo "✅ Frontend assets built successfully"

# 4. Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force
echo "✅ Database migrations completed"

# 5. Cache optimization
echo "⚡ Optimizing application cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Cache optimization completed"

# 6. Clear old caches
echo "🧹 Clearing old caches..."
php artisan cache:clear
php artisan optimize:clear
echo "✅ Old caches cleared"

# 7. Optimize application
echo "🚀 Optimizing application..."
php artisan optimize
echo "✅ Application optimized"

echo ""
echo "✅ Deployment completed successfully!"
