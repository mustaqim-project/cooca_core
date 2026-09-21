<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\WhatsApp\AdminWhatsAppService;
use Illuminate\Console\Command;

/**
 * Sinkronisasi Message Templates dari Meta WhatsApp Cloud API ke database lokal.
 *
 * Dijalankan secara terjadwal setiap 1 jam (hourly) atau manual:
 *   php artisan whatsapp:sync-templates
 *   php artisan whatsapp:sync-templates --waba=1546059137323420
 */
class SyncWhatsAppTemplatesCommand extends Command
{
    protected $signature = 'whatsapp:sync-templates {--waba= : Override WABA ID}';

    protected $description = 'Sinkronisasi template pesan dari Meta WhatsApp Cloud API ke database lokal';

    public function handle(AdminWhatsAppService $service): int
    {
        $wabaId = $this->option('waba') ?: null;

        $this->components->info('Memulai sinkronisasi template dari Meta Cloud API...');

        $result = $service->syncTemplatesFromMeta($wabaId);

        if (!($result['success'] ?? false)) {
            $this->components->error('Gagal: ' . ($result['error'] ?? 'Unknown error'));
            return self::FAILURE;
        }

        $this->components->info("Sinkronisasi selesai: {$result['created']} baru, {$result['updated']} diperbarui (total dari Meta: {$result['total']})");

        return self::SUCCESS;
    }
}
