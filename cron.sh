#!/usr/bin/env bash
# ==============================================================================
# COOCA ERP - Automated Cron Scheduler Runner (cron.sh)
# ==============================================================================
# This script executes Laravel's scheduler (php artisan schedule:run).
# It automates:
#   1. Daily subscription expiration and lifecycle processing (00:01 WIB)
#   2. Daily AI token monthly allowances reset (00:05 WIB)
#   3. Daily WhatsApp expiry reminders: H-7, H-3, H-1, H-Day (09:00 WIB)
#   4. Every-minute background publishing for scheduled social media posts
#
# Production crontab setup:
#   1. Give execute permission:
#      chmod +x /path/to/cooca_core/cron.sh
#   2. Edit crontab:
#      crontab -e
#   3. Add this line (runs every minute):
#      * * * * * cd /path/to/cooca_core && ./cron.sh >> /dev/null 2>&1
# ==============================================================================

set -e

# Resolve directory of this script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Detect PHP CLI binary
PHP_BIN=""
if command -v php >/dev/null 2>&1; then
    PHP_BIN="$(command -v php)"
elif [ -x "/usr/bin/php" ]; then
    PHP_BIN="/usr/bin/php"
elif [ -x "/usr/local/bin/php" ]; then
    PHP_BIN="/usr/local/bin/php"
elif [ -x "/usr/bin/php8.3" ]; then
    PHP_BIN="/usr/bin/php8.3"
elif [ -x "/usr/bin/php8.2" ]; then
    PHP_BIN="/usr/bin/php8.2"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: PHP CLI binary not found." >&2
    exit 1
fi

# Ensure storage/logs directory exists
mkdir -p "$SCRIPT_DIR/storage/logs"

# Execute Laravel Schedule Run
"$PHP_BIN" artisan schedule:run >> "$SCRIPT_DIR/storage/logs/scheduler.log" 2>&1
