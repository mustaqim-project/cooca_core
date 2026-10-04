<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BugReport;
use App\Models\Business;
use App\Models\FeatureRequest;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class FeedbackWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::create(['name' => 'Business Owner', 'email' => 'owner@feedback.test', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Feedback Business']);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
        $this->admin = Admin::create(['name' => 'Feedback Admin', 'email' => 'admin@feedback.test', 'password' => Hash::make('password'), 'role' => 'super_admin', 'is_active' => true]);
    }

    public function test_owner_can_submit_bug_and_admin_can_track_progress(): void
    {
        $response = $this->actingAs($this->owner)->post(route('feedback.bugs.store'), [
            'title' => 'Stok tidak tersimpan', 'category' => 'bug', 'severity' => 'high',
            'description' => 'Perubahan stok hilang setelah refresh.', 'steps_to_reproduce' => 'Buka stok lalu simpan.',
            'expected_behavior' => 'Stok tersimpan.', 'actual_behavior' => 'Stok kembali ke nilai lama.', 'environment' => 'Chrome Windows',
        ]);
        $bug = BugReport::firstOrFail();
        $response->assertRedirect(route('feedback.bugs.show', $bug));
        $this->assertDatabaseHas('feedback_updates', ['trackable_id' => $bug->id, 'status' => 'open']);

        $response = $this->actingAs($this->admin, 'admin')->patch(route('admin.feedback.bugs.update', $bug), [
            'status' => 'in_progress', 'priority' => 'high', 'progress_percent' => 60,
            'assigned_admin_id' => $this->admin->id, 'comment' => 'Sedang direproduksi oleh tim engineering.', 'resolution' => '',
        ]);
        $response->assertSessionHas('success');
        $bug->refresh();
        $this->assertSame('in_progress', $bug->status);
        $this->assertSame(60, $bug->progress_percent);
        $this->assertCount(2, $bug->updates()->get());
    }

    public function test_owner_can_submit_feature_request_and_admin_can_release_it(): void
    {
        $response = $this->actingAs($this->owner)->post(route('feedback.features.store'), [
            'title' => 'Export laporan ke Excel', 'category' => 'reporting', 'priority' => 'normal',
            'description' => 'Owner membutuhkan export laporan.', 'business_value' => 'Mempercepat rekonsiliasi.',
            'use_case' => 'Owner memilih periode lalu mengunduh file.', 'proposed_solution' => 'Tambahkan tombol export.',
        ]);
        $feature = FeatureRequest::firstOrFail();
        $response->assertRedirect(route('feedback.features.show', $feature));

        $this->actingAs($this->admin, 'admin')->patch(route('admin.feedback.features.update', $feature), [
            'status' => 'released', 'priority' => 'high', 'progress_percent' => 100,
            'assigned_admin_id' => $this->admin->id, 'comment' => 'Fitur sudah dirilis.', 'admin_notes' => 'Tersedia mulai versi berikutnya.',
        ])->assertSessionHas('success');
        $feature->refresh();
        $this->assertSame('released', $feature->status);
        $this->assertNotNull($feature->released_at);
        $this->assertCount(2, $feature->updates()->get());
    }

    public function test_bug_reports_and_features_are_isolated_by_belongs_to_business_scope(): void
    {
        // Business B setup
        $ownerB = User::create(['name' => 'Owner B', 'email' => 'ownerb@feedback.test', 'password' => 'password']);
        $businessB = Business::create(['name' => 'Business B']);
        $businessB->users()->attach($ownerB->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $ownerB->update(['active_business_id' => $businessB->id]);

        // Create bug & feature for Business B
        Context::setBusiness($businessB);
        $bugB = BugReport::create([
            'reporter_id' => $ownerB->id,
            'title' => 'Bug Milik Bisnis B',
            'category' => 'bug',
            'severity' => 'normal',
            'description' => 'Deskripsi bug B',
            'status' => 'open',
            'priority' => 'normal',
            'progress_percent' => 0,
        ]);
        $featureB = FeatureRequest::create([
            'requester_id' => $ownerB->id,
            'title' => 'Feature Milik Bisnis B',
            'category' => 'sales',
            'description' => 'Deskripsi feature B',
            'business_value' => 'Nilai bisnis B',
            'use_case' => 'Use case B',
            'status' => 'submitted',
            'priority' => 'normal',
            'progress_percent' => 0,
        ]);

        $this->assertSame($businessB->id, $bugB->business_id);
        $this->assertSame($businessB->id, $featureB->business_id);

        // Switch context to Business A
        Context::setBusiness($this->business);

        // BelongsToBusiness global BusinessScope should exclude Business B items
        $this->assertCount(0, BugReport::where('id', $bugB->id)->get());
        $this->assertCount(0, FeatureRequest::where('id', $featureB->id)->get());

        // HTTP request to show Business B item from Business A should be rejected with 404 (or 403)
        $this->actingAs($this->owner)->get(route('feedback.bugs.show', $bugB->id))->assertNotFound();
        $this->actingAs($this->owner)->get(route('feedback.features.show', $featureB->id))->assertNotFound();
    }

    public function test_feedback_pages_render_3_row_apple_hig_header_tabs_and_breadcrumbs(): void
    {
        Context::setBusiness($this->business);

        // 1. Bugs Index
        $response = $this->actingAs($this->owner)->get(route('feedback.bugs.index'));
        $response->assertOk();
        $response->assertSee('data-module-tabs="feedback"', false);
        $response->assertSee('Laporan Kendala (Bugs)');
        $response->assertSee('Request Fitur');
        $response->assertSee(route('feedback.bugs.create'));

        // 2. Features Index
        $response = $this->actingAs($this->owner)->get(route('feedback.features.index'));
        $response->assertOk();
        $response->assertSee('data-module-tabs="feedback"', false);
        $response->assertSee(route('feedback.features.create'));

        // 3. Create Bug
        $response = $this->actingAs($this->owner)->get(route('feedback.bugs.create'));
        $response->assertOk();
        $response->assertSee('data-module-tabs="feedback"', false);
        $response->assertSee(route('feedback.bugs.index'));

        // 4. Create Feature
        $response = $this->actingAs($this->owner)->get(route('feedback.features.create'));
        $response->assertOk();
        $response->assertSee('data-module-tabs="feedback"', false);
        $response->assertSee(route('feedback.features.index'));

        // 5. Show Bug
        $bug = BugReport::create([
            'business_id' => $this->business->id,
            'reporter_id' => $this->owner->id,
            'title' => 'Kendala Form POS Macet',
            'category' => 'bug',
            'severity' => 'high',
            'description' => 'Tombol cetak struk tidak merespon.',
            'status' => 'open',
            'priority' => 'high',
            'progress_percent' => 10,
        ]);
        $response = $this->actingAs($this->owner)->get(route('feedback.bugs.show', $bug));
        $response->assertOk();
        $response->assertSee('data-module-tabs="feedback"', false);
        $response->assertSee('Kendala Form POS Macet');
        $response->assertSee(route('feedback.bugs.index'));

        // 6. Show Feature
        $feature = FeatureRequest::create([
            'business_id' => $this->business->id,
            'requester_id' => $this->owner->id,
            'title' => 'Dukungan Bluetooth Thermal Printer',
            'category' => 'pos',
            'description' => 'Integrasi printer struk via bluetooth.',
            'business_value' => 'Mobilitas kasir lebih fleksibel.',
            'use_case' => 'Kasir membawa tablet keliling meja.',
            'status' => 'submitted',
            'priority' => 'normal',
            'progress_percent' => 0,
        ]);
        $response = $this->actingAs($this->owner)->get(route('feedback.features.show', $feature));
        $response->assertOk();
        $response->assertSee('data-module-tabs="feedback"', false);
        $response->assertSee('Dukungan Bluetooth Thermal Printer');
        $response->assertSee(route('feedback.features.index'));
    }
}
