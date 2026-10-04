<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Test suite for verifying Phase 7 Mobile-First Responsive Ergonomics & Safari iOS Anti-Zoom.
 */
class PosAuditPhase7ResponsiveErgonomicsTest extends TestCase
{
    /**
     * Test terminal.blade.php includes Safari iOS viewport anti-zoom CSS rules.
     */
    public function test_terminal_blade_includes_safari_anti_zoom_css(): void
    {
        $content = File::get(resource_path('views/app/pos/terminal.blade.php'));

        $this->assertStringContainsString(
            '@media screen and (max-width: 768px)',
            $content,
            'terminal.blade.php must include media query for mobile screen widths <= 768px.'
        );

        $this->assertStringContainsString(
            'font-size: 16px !important;',
            $content,
            'terminal.blade.php must enforce 16px font-size on mobile inputs to prevent iOS Safari auto-zoom.'
        );
    }

    /**
     * Test terminal.blade.php has adaptive modal panel sizing for small viewports.
     */
    public function test_terminal_blade_has_adaptive_modal_panel_sizing(): void
    {
        $content = File::get(resource_path('views/app/pos/terminal.blade.php'));

        $this->assertStringContainsString(
            'max-width: min(calc(100vw - 1rem), 42rem)',
            $content,
            'terminal.blade.php modal panels must use adaptive max-width for small viewports.'
        );

        $this->assertStringContainsString(
            'max-height: min(90dvh, calc(100vh - 1.5rem))',
            $content,
            'terminal.blade.php modal panels must respect viewport height constraints.'
        );
    }

    /**
     * Test orders.blade.php has touch-friendly action buttons with min-h-[48px].
     */
    public function test_orders_blade_has_touch_friendly_tap_targets(): void
    {
        $content = File::get(resource_path('views/app/pos/orders.blade.php'));

        // Header action button
        $this->assertStringContainsString(
            'min-h-[48px]',
            $content,
            'orders.blade.php must specify touch target min-h-[48px] for mobile actions.'
        );
    }

    /**
     * Test kitchen.blade.php has touch-friendly action buttons with min-h-[48px].
     */
    public function test_kitchen_blade_has_touch_friendly_tap_targets(): void
    {
        $content = File::get(resource_path('views/app/pos/kitchen.blade.php'));

        $this->assertStringContainsString(
            'min-h-[48px]',
            $content,
            'kitchen.blade.php must specify touch target min-h-[48px] for action buttons.'
        );

        // Verify kitchen order status change buttons
        $this->assertStringContainsString(
            'min-h-[48px] h-12',
            $content,
            'kitchen.blade.php card status buttons must be at least 48px in height.'
        );
    }
}
