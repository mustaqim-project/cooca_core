<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\WhatsApp\WhatsAppService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\User;
use InvalidArgumentException;

final class WhatsappSendNotificationTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'whatsapp_send_notification';
    }

    public function getDescription(): string
    {
        return 'Mengirimkan pesan WhatsApp resmi dari nomor bisnis ke pelanggan atau staf (struk, pengingat janji temu, penawaran harga).';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:whatsapp:send';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'phone_number' => [
                    'type' => 'string',
                    'description' => 'Nomor WhatsApp tujuan dengan kode negara (contoh: 6281234567890 atau 081234567890).',
                ],
                'message' => [
                    'type' => 'string',
                    'description' => 'Isi pesan teks WhatsApp.',
                ],
            ],
            'required' => ['phone_number', 'message'],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $phone = trim((string) ($arguments['phone_number'] ?? ''));
        $message = trim((string) ($arguments['message'] ?? ''));

        if ($phone === '' || $message === '') {
            throw new InvalidArgumentException('Nomor WhatsApp dan isi pesan wajib diisi.');
        }

        $waService = app(WhatsAppService::class);
        $formattedPhone = $waService->formatPhoneNumber($phone);

        $sent = $waService->sendMessage($formattedPhone, $message);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp.whatsapp_send_notification',
            'auditable_type' => Business::class,
            'auditable_id' => $business->id,
            'old_values' => null,
            'new_values' => [
                'phone' => $formattedPhone,
                'message_preview' => mb_strimwidth($message, 0, 60, '...'),
                'delivered' => $sent,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
        ]);

        return [
            'status' => 'success',
            'delivered' => $sent,
            'target_phone' => $formattedPhone,
            'message_preview' => mb_strimwidth($message, 0, 80, '...'),
            'message' => "Pesan WhatsApp berhasil dikirimkan ke {$formattedPhone}.",
        ];
    }
}
