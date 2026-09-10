<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Models\Business;
use App\Models\PosTable;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

final class TableQrCodeService
{
    /**
     * Generate an SVG QR code string with high error correction (Level H)
     * and optionally embed the business logo centered with a protective white halo.
     */
    public function generateSvg(string $url, ?string $logoPathOrUrl = null, int $size = 400): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        // Error Correction Level H (30% fault tolerance) guarantees 100% scan reliability with centered logo
        $svg = $writer->writeString($url, 'utf-8', ErrorCorrectionLevel::H());

        // If no logo is specified, return clean SVG
        if (! $logoPathOrUrl) {
            return $svg;
        }

        // Resolve logo data URI or URL
        $logoDataUri = $this->resolveLogoDataUri($logoPathOrUrl);
        if (! $logoDataUri) {
            return $svg;
        }

        // Center calculation
        $logoSize = (int) ($size * 0.22); // 22% of total size
        $bgSize = (int) ($logoSize + 12); // protective white margin
        $xCenter = (int) ($size / 2);
        $yCenter = (int) ($size / 2);

        $bgX = $xCenter - (int) ($bgSize / 2);
        $bgY = $yCenter - (int) ($bgSize / 2);
        $logoX = $xCenter - (int) ($logoSize / 2);
        $logoY = $yCenter - (int) ($logoSize / 2);
        $radius = (int) ($bgSize * 0.25);

        // Inject logo group right before closing </svg>
        $logoGroup = <<<SVG
    <g id="qr-logo-center">
        <!-- Protective White Background Pad -->
        <rect x="{$bgX}" y="{$bgY}" width="{$bgSize}" height="{$bgSize}" rx="{$radius}" ry="{$radius}" fill="#FFFFFF" stroke="#E5E5EA" stroke-width="1.5" />
        <!-- Centered Business Logo -->
        <image x="{$logoX}" y="{$logoY}" width="{$logoSize}" height="{$logoSize}" href="{$logoDataUri}" preserveAspectRatio="xMidYMid meet" />
    </g>
</svg>
SVG;

        return preg_replace('/<\/svg>/i', $logoGroup, $svg, 1) ?? $svg;
    }

    /**
     * Generate QR code for a PosTable model.
     */
    public function generateForTable(PosTable $table, ?Business $business = null, int $size = 400): string
    {
        $url = $table->qr_url;
        $biz = $business ?? $table->business;
        $logo = $biz?->logo_path ?? null;

        return $this->generateSvg($url, $logo, $size);
    }

    /**
     * Resolve logo to a data URI if local, or clean URL if external.
     */
    private function resolveLogoDataUri(string $logoPathOrUrl): ?string
    {
        // 1. If it's already a data URI or external http URL
        if (str_starts_with($logoPathOrUrl, 'data:image/') || str_starts_with($logoPathOrUrl, 'http://') || str_starts_with($logoPathOrUrl, 'https://')) {
            return $logoPathOrUrl;
        }

        // 2. If it's a storage path in storage/app/public
        if (Storage::disk('public')->exists($logoPathOrUrl)) {
            $mime = Storage::disk('public')->mimeType($logoPathOrUrl) ?: 'image/png';
            $content = Storage::disk('public')->get($logoPathOrUrl);
            return 'data:' . $mime . ';base64,' . base64_encode($content);
        }

        // 3. If file exists in public/storage
        $publicPath = public_path('storage/' . $logoPathOrUrl);
        if (file_exists($publicPath)) {
            $mime = mime_content_type($publicPath) ?: 'image/png';
            $content = file_get_contents($publicPath);
            return 'data:' . $mime . ';base64,' . base64_encode($content ?: '');
        }

        return null;
    }
}
