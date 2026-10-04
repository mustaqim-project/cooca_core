<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SharedNavigationComponentsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Component Test',
            'email'             => 'owner_comp_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'     => 'Bisnis Shared Component Audit',
            'currency' => 'IDR',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
        $this->actingAs($this->owner, 'web');
    }

    public function test_breadcrumb_component_renders_items(): void
    {
        $rendered = Blade::render('<x-breadcrumb :items="$items" />', [
            'items' => [
                ['label' => 'Home', 'url' => '/dashboard'],
                ['label' => 'Keuangan', 'url' => '/finance/cash-bank'],
                ['label' => 'Beban Operasional', 'url' => null],
            ],
        ]);

        $this->assertStringContainsString('Home', $rendered);
        $this->assertStringContainsString('Keuangan', $rendered);
        $this->assertStringContainsString('Beban Operasional', $rendered);
        $this->assertStringContainsString('chevron-right', $rendered);
    }

    public function test_module_header_component_renders_title_and_actions(): void
    {
        $rendered = Blade::render('
            <x-module-header
                title="Katalog Bahan Baku"
                subtitle="Kelola seluruh inventori bahan baku mentah"
                :breadcrumbs="[
                    [\'label\' => \'Home\', \'url\' => \'/dashboard\'],
                    [\'label\' => \'Bahan Baku\', \'url\' => null],
                ]"
            >
                <button id="test-btn">Tambah Bahan</button>
            </x-module-header>
        ');

        $this->assertStringContainsString('Katalog Bahan Baku', $rendered);
        $this->assertStringContainsString('Kelola seluruh inventori bahan baku mentah', $rendered);
        $this->assertStringContainsString('Tambah Bahan', $rendered);
        $this->assertStringContainsString('<header', $rendered);
    }

    public function test_module_tabs_component_renders_persistent_tabs_for_materials(): void
    {
        $rendered = Blade::render('<x-module-tabs module="materials" />');

        $this->assertStringContainsString('Katalog Bahan Baku', $rendered);
        $this->assertStringContainsString('Kategori Bahan', $rendered);
        $this->assertStringContainsString('Satuan Ukur (Units)', $rendered);
    }

    public function test_module_tabs_component_renders_persistent_tabs_for_finance(): void
    {
        $rendered = Blade::render('<x-module-tabs module="finance" />');

        $this->assertStringContainsString('Kas &amp; Rekening', $rendered);
        $this->assertStringContainsString('Buku Kas &amp; Mutasi', $rendered);
        $this->assertStringContainsString('Beban Operasional', $rendered);
        $this->assertStringContainsString('Jurnal Akuntansi', $rendered);
        $this->assertStringContainsString('Piutang (AR)', $rendered);
        $this->assertStringContainsString('Hutang (AP)', $rendered);
        $this->assertStringContainsString('Payout Hub', $rendered);
    }

    public function test_module_tabs_component_renders_persistent_tabs_for_communication(): void
    {
        $rendered = Blade::render('<x-module-tabs module="communication" />');

        $this->assertStringContainsString('Koneksi Akun', $rendered);
        $this->assertStringContainsString('Postingan Konten', $rendered);
        $this->assertStringContainsString('Kalender Jadwal', $rendered);
        $this->assertStringContainsString('Kotak Masuk', $rendered);
        $this->assertStringContainsString('Analitik Metrik', $rendered);
        $this->assertStringContainsString('WhatsApp Gateway', $rendered);
        $this->assertStringContainsString('Siaran Pesan (Broadcast)', $rendered);
    }
}

