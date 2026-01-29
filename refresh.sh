#!/bin/sh

echo "🔄 Starting application refresh..."

# Get the script's directory
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "📁 Working directory: $SCRIPT_DIR"

# 1. Clear configuration cache
echo "🧹 Clearing configuration cache..."
php artisan config:clear

# 2. Clear application cache
echo "🧹 Clearing application cache..."
php artisan cache:clear

# 3. Clear route cache
echo "🧹 Clearing route cache..."
php artisan route:clear

# 4. Clear view cache
echo "🧹 Clearing view cache..."
php artisan view:clear

# 5. Clear compiled files
echo "🧹 Clearing compiled files..."
php artisan optimize:clear

# 6. Rebuild caches
echo "⚡ Rebuilding configuration cache..."
php artisan config:cache

# 7. Build frontend assets
echo "🎨 Building frontend assets..."
npm run build

# 8. Restart queue workers if running
echo "🔄 Restarting queue workers..."
php artisan queue:restart 2>/dev/null || echo "⚠️  Queue workers not running"

echo ""
echo "✅ Application refresh completed successfully!"
