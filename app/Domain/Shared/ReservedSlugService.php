<?php

declare(strict_types=1);

namespace App\Domain\Shared;

final class ReservedSlugService
{
    /**
     * Complete registry of reserved root URL slugs and platform system prefixes.
     * Any tenant trying to use these slugs will have their slug sanitized (e.g. 'login-store').
     *
     * @var array<string>
     */
    public const RESERVED_SLUGS = [
        // 1. Core Authentication & Account
        'login',
        'logout',
        'register',
        'auth',
        'account-recovery',
        'forgot-password',
        'reset-password',
        'complete-profile',
        'select-business',
        'sanctum',
        'password',
        'email',
        'oauth',
        'sso',

        // 2. Platform Admin & Operations
        'admin',
        'superadmin',
        'backoffice',
        'ops',
        'api',
        'app',
        'up',
        '_laravel-brain',

        // 3. Modules & Workspaces
        'dashboard',
        'pos',
        'hrm',
        'billing',
        'finance',
        'inventory',
        'sales',
        'purchasing',
        'purchase-orders',
        'invoices',
        'suppliers',
        'customers',
        'customer',
        'crm',
        'products',
        'product-categories',
        'services',
        'warehouse',
        'materials',
        'material-categories',
        'material-unit-conversions',
        'machines',
        'labor-rates',
        'labor-machines',
        'bom-headers',
        'bom-items',
        'units',
        'unit-conversions',
        'reports',
        'profitability',
        'tax',
        'import',
        'settings',
        'roles',
        'profile',
        'social-media',
        'whatsapp',
        'storefront',
        'settlements',
        'feedback',
        'community',
        'patungan',
        'b',

        // 4. Public Web, Lead Capture, Marketing & Legal
        'jelajah',
        'direktori',
        'kalkulator',
        'kalkulator-hpp',
        'kalkulator-bep',
        'kalkulator-harga-jual',
        'kalkulator-laba-bersih',
        'kalkulator-gaji-karyawan',
        'kalkulator-pph-final',
        'kalkulator-omzet-harian',
        'simulasi-what-if',
        'simulator',
        'calculator',
        'template',
        'template-pembukuan-gratis',
        'solusi',
        'blog',
        'kontak',
        'contact',
        'sitemap',
        'sitemap.xml',
        'robots.txt',
        'privacy',
        'kebijakan-privasi',
        'terms',
        'syarat-ketentuan',
        'receipt',
        'payslip',
        'landing-page',
        'onboarding',
        'businesses',
        'storage',
        'assets',
        'css',
        'js',
        'images',
        'static',
        'webhook',
        'webhooks',
        't',
        'order',
        'cart',
        'checkout',
    ];

    /**
     * Check whether a given slug collides with a reserved platform keyword.
     */
    public static function isReserved(string $slug): bool
    {
        $normalized = strtolower(trim($slug));

        return in_array($normalized, self::RESERVED_SLUGS, true);
    }

    /**
     * Sanitize a slug if it is reserved by appending a safe suffix (e.g. 'admin' -> 'admin-store').
     */
    public static function sanitize(string $slug, string $suffix = 'store'): string
    {
        $normalized = strtolower(trim($slug));

        if (! self::isReserved($normalized)) {
            return $normalized;
        }

        return "{$normalized}-{$suffix}";
    }

    /**
     * Get all reserved slugs.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return self::RESERVED_SLUGS;
    }
}
