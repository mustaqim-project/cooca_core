#!/bin/sh
set -e
cd /home/u218101292/domains/cooca.id/public_html

echo "=========================================="
echo "COOCA ERP - Production Deploy Script"
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="

# 1. Pull latest code from GitHub main branch
if [ -d ".git" ]; then
    echo "Pulling latest code from origin main..."
    git pull origin main || echo "Git pull notice: continuing with current files."
fi

# 2. Resolve PHP CLI binary (Hostinger default is /opt/alt/php83/usr/bin/php)
PHP_BIN="/opt/alt/php83/usr/bin/php"
if [ ! -x "$PHP_BIN" ]; then
    PHP_BIN="$(command -v php)"
fi

echo "Using PHP Binary: $($PHP_BIN -v | head -n 1)"

# 3. Run database migrations
echo "Running database migrations..."
$PHP_BIN artisan migrate --force

# 4. Seed core system data (idempotent)
echo "Seeding core defaults & RBAC matrix..."
$PHP_BIN artisan db:seed --class=RbacSeeder --force
$PHP_BIN artisan db:seed --class=DefaultUnitSeeder --force
$PHP_BIN artisan db:seed --class=DefaultCostCategorySeeder --force
$PHP_BIN artisan db:seed --class=BusinessTemplateSeeder --force
$PHP_BIN artisan db:seed --class=PostSeeder --force

# 5. Clear and cache configuration, routes, and views for maximum production performance
echo "Optimizing application caches..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# 6. Ensure proper storage permissions
echo "Checking storage permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "=========================================="
echo "Deployment completed successfully!"
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="
