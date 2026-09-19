#!/bin/sh
set -e
cd /home/u218101292/domains/cooca.id/public_html

PHP_BIN="/opt/alt/php83/usr/bin/php"
if [ ! -x "$PHP_BIN" ]; then
    PHP_BIN="$(command -v php)"
fi

echo "Deploying update: $(date)"
$PHP_BIN -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); App\Models\PaymentAccount::truncate(); echo 'Payment accounts purged.\n';"
$PHP_BIN artisan optimize:clear
echo "Deploy complete: $(date)"
