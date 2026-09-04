<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Exceptions;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly string $productName,
        public readonly float $availableStock,
        public readonly float $requestedQuantity,
        public readonly string $locationName = ''
    ) {
        $msg = "Stok tidak mencukupi untuk item \"{$productName}\". Tersedia: {$availableStock}, Diminta: {$requestedQuantity}.";
        if ($locationName !== '') {
            $msg .= " (Lokasi: {$locationName})";
        }
        parent::__construct($msg);
    }
}
