<?php

declare(strict_types=1);

namespace App\Domain\Printer;

final class PrinterIpValidator
{
    /**
     * Determine if a given IP address or hostname is safe for LAN printer communication.
     * Blocks loopback, link-local, private cloud metadata (169.254.169.254), broadcast, and invalid addresses.
     */
    public static function isSafe(string $address): bool
    {
        $address = trim($address);

        if ($address === '') {
            return false;
        }

        $lower = strtolower($address);

        // Explicitly block localhost and cloud metadata endpoints
        if (in_array($lower, [
            'localhost',
            '127.0.0.1',
            '::1',
            '0.0.0.0',
            '169.254.169.254',
            'metadata.google.internal',
            'instance-data',
        ], true)) {
            return false;
        }

        // IPv4 validation
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Loopback (127.0.0.0/8)
            if (str_starts_with($address, '127.')) {
                return false;
            }

            // Link-local / AWS / GCP / Azure Cloud Metadata (169.254.0.0/16)
            if (str_starts_with($address, '169.254.')) {
                return false;
            }

            // Current network / 0.0.0.0/8
            if (str_starts_with($address, '0.')) {
                return false;
            }

            // Broadcast (255.255.255.255)
            if ($address === '255.255.255.255') {
                return false;
            }

            return true;
        }

        // IPv6 validation
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Loopback (::1) or Link-Local (fe80::)
            if ($address === '::1' || str_starts_with($lower, 'fe80:')) {
                return false;
            }
            return true;
        }

        // Local LAN hostname validation (e.g., printer-kasir.local, epson-kitchen.lan)
        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-\.]{1,253}[a-zA-Z0-9]$/', $address)) {
            if (str_ends_with($lower, '.internal') || str_contains($lower, 'metadata') || str_contains($lower, 'localhost')) {
                return false;
            }
            return true;
        }

        return false;
    }
}
