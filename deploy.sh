#!/bin/sh
set -e
cd /home/u218101292/domains/cooca.id/public_html

PHP_BIN="/opt/alt/php83/usr/bin/php"
if [ ! -x "$PHP_BIN" ]; then
    PHP_BIN="$(command -v php)"
fi

echo "Deploying update: $(date)"
$PHP_BIN artisan migrate --force
$PHP_BIN artisan db:seed --class=RbacSeeder --force
$PHP_BIN artisan db:seed --class=BusinessTemplateSeeder --force
$PHP_BIN artisan db:seed --class=PostSeeder --force
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan view:cache
echo "Deploy complete: $(date)"
