<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = config('services.meta_whatsapp.token');
$phoneNumberId = config('services.meta_whatsapp.phone_number_id');
$wabaId = config('services.meta_whatsapp.waba_id');
$version = config('services.meta_whatsapp.version');

echo "Token (first 30): " . substr($token, 0, 30) . "...\n";
echo "Phone Number ID: $phoneNumberId\n";
echo "WABA ID: $wabaId\n";
echo "Version: $version\n";
echo "Sending text to 6282114468467...\n\n";

$client = new \App\Domain\WhatsApp\CloudApi\WhatsAppClient(
    accessToken: $token,
    phoneNumberId: $phoneNumberId,
    wabaId: $wabaId
);

try {
    $result = $client->sendText('6282114468467', 'Halo! Ini pesan tes dari Cooca WABA v21.0. Integrasi WhatsApp Business API berhasil! - Cooca.id');
    echo "SUCCESS!\n";
    print_r($result);
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getResponse')) {
        echo "Response: " . $e->getResponse()->getBody()->getContents() . "\n";
    }
}
