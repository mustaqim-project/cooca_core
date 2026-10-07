<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payment = \App\Models\SubscriptionPayment::first();
if (!$payment) {
    echo "No payment found\n";
    exit(0);
}

$business = $payment->business;
$methodDetails = $payment->getPaymentMethodDetails();

$html = view('app.billing.invoice', compact('business', 'payment', 'methodDetails'))->render();
echo "RENDER SUCCESS, output length: " . strlen($html) . "\n";
echo "Contains logo base64: " . (str_contains($html, 'data:image/png;base64') ? "YES" : "NO") . "\n";
echo "Contains Informas Transaksi: " . (str_contains($html, 'Informasi Transaksi &amp; Pembayaran') ? "YES" : "NO") . "\n";
