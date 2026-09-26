#!/bin/bash
# ==============================================================================
# COOCA ERP - One-Click Hostinger Production Setup Script
# ==============================================================================
set -e

cd /home/u218101292/domains/cooca.id/public_html

echo "=========================================="
echo "🚀 Memulai Setup Server Hostinger Cooca ERP"
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="

PHP_BIN="/opt/alt/php83/usr/bin/php"
if [ ! -x "$PHP_BIN" ]; then
    PHP_BIN="$(command -v php)"
fi
echo "✓ Menggunakan PHP: $($PHP_BIN -v | head -n 1)"

# 1. Update Git Remote URL dengan Token Otentikasi
echo "✓ Memperbarui kredensial Git..."
git remote set-url origin https://mustaqim-project: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@github.com/mustaqim-project/cooca_core.git
git pull origin main || true

# 2. Penyiapan File .env
if [ ! -f .env ]; then
    echo "✓ Membuat file .env dari .env.example..."
    cp .env.example .env
fi

# Pastikan variabel APP_KEY tersedia di .env sebelum key:generate
if ! grep -q "^APP_KEY=" .env; then
    echo "✓ Menambahkan variabel APP_KEY= ke .env..."
    echo "" >> .env
    echo "APP_KEY=" >> .env
fi

# 3. Generate Application Key jika masih kosong
APP_KEY_VAL=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY_VAL" ]; then
    echo "✓ Menghasilkan APP_KEY baru..."
    $PHP_BIN artisan key:generate --force
fi

# 4. Install Dependensi Composer
echo "✓ Menginstall dependensi Composer (mode produksi)..."
$PHP_BIN /usr/local/bin/composer install --no-dev --optimize-autoloader

# 5. Database Migrations & System Seeders
echo "✓ Menjalankan migrasi database..."
$PHP_BIN artisan migrate --force

echo "✓ Menjalankan seeding data sistem (RBAC, Templates, Defaults)..."
$PHP_BIN artisan db:seed --class=RbacSeeder --force
$PHP_BIN artisan db:seed --class=DefaultUnitSeeder --force
$PHP_BIN artisan db:seed --class=DefaultCostCategorySeeder --force
$PHP_BIN artisan db:seed --class=BusinessTemplateSeeder --force
$PHP_BIN artisan db:seed --class=PostSeeder --force

# 6. Storage Link & Folder Permissions
echo "✓ Mengatur symlink storage dan izin direktori..."
$PHP_BIN artisan storage:link || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 7. Production Caching
echo "✓ Mengoptimalkan cache produksi Laravel..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

echo "=========================================="
echo "✅ Setup Server Hostinger Berhasil Selesai!"
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="
