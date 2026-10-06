<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\HRM\Biometrics\FaceVerificationService;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HrmFaceBiometricAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $ownerUser;
    private User $employeeUser;
    private BusinessMembership $employeeMembership;
    private Location $primaryLocation;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Create Business
        $this->business = Business::create([
            'name' => 'Cooca Bakery & Coffee',
            'slug' => 'cooca-bakery-coffee',
            'email' => 'contact@coocabakery.com',
            'phone' => '081298765432',
            'city' => 'Jakarta Selatan',
            'address' => 'Jl. Senopati No. 88, Jakarta Selatan',
            'is_active' => true,
        ]);

        // Active Prestige Subscription for Unlimited Staff & Locations
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PRESTIGE_MONTHLY,
            'price' => 199000,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addYear(),
        ]);

        // 2. Primary Location with Geofence Coordinates
        $this->primaryLocation = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Senopati Utama',
            'code' => 'LOC-SNT-01',
            'type' => 'outlet',
            'latitude' => -6.229700,
            'longitude' => 106.807400,
            'geofence_radius_meters' => 50,
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 3. Owner User
        $this->ownerUser = User::create([
            'name' => 'Owner Cooca',
            'email' => 'owner@coocabakery.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081298765431',
            'active_business_id' => $this->business->id,
        ]);

        $this->business->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'job_title' => 'Chief Executive Officer',
            'is_active' => true,
        ]);

        // 4. Employee User
        $this->employeeUser = User::create([
            'name' => 'Dewi Barista',
            'email' => 'dewi@coocabakery.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081298765433',
            'active_business_id' => $this->business->id,
        ]);

        $role = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Senior Barista',
            'slug' => 'senior-barista',
            'is_system' => false,
        ]);

        $managePerm = Permission::firstOrCreate(['slug' => 'users.manage'], ['name' => 'Manage Users', 'module' => 'users']);
        $viewPerm = Permission::firstOrCreate(['slug' => 'users.view'], ['name' => 'View Users', 'module' => 'users']);
        RolePermission::create(['role_id' => $role->id, 'permission_id' => $viewPerm->id]);

        $this->business->users()->attach($this->employeeUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $role->id,
            'primary_location_id' => $this->primaryLocation->id,
            'job_title' => 'Senior Barista',
            'employment_type' => 'permanent',
            'base_salary' => 4500000,
            'fixed_allowances' => 500000,
            'variable_allowances' => 250000,
            'nik_ktp' => '3271012345670001',
            'npwp' => '09.123.456.7-001.000',
            'bpjs_tk_enabled' => true,
            'bpjs_tk_number' => 'TK-987654321',
            'bpjs_kes_enabled' => true,
            'bpjs_kes_number' => 'KES-123456789',
            'bpjs_dependents_count' => 2,
            'tax_ptkp_status' => 'K/2',
            'bank_name' => 'BCA',
            'bank_account_number' => '8820192837',
            'bank_account_holder' => 'Dewi Barista',
            'is_active' => true,
        ]);

        $this->employeeMembership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->employeeUser->id)
            ->firstOrFail();
    }

    /**
     * Test storing employee with complete BPJS TK, BPJS Kesehatan, Tanggungan, NIK, and NPWP.
     */
    public function test_owner_can_store_employee_with_bpjs_and_dependents(): void
    {
        $role = Role::where('business_id', $this->business->id)->firstOrFail();

        $response = $this->actingAs($this->ownerUser)
            ->post(route('hrm.employees.store'), [
                'name' => 'Rian Kurnia',
                'email' => 'rian@coocabakery.com',
                'role_id' => $role->id,
                'job_title' => 'Junior Baker',
                'employment_type' => 'contract',
                'nik_ktp' => '3271019988770002',
                'npwp' => '08.987.654.3-002.000',
                'attendance_mode' => 'geofenced',
                'primary_location_id' => $this->primaryLocation->id,
                'base_salary' => 4000000,
                'join_date' => Carbon::now()->toDateString(),
                'tax_ptkp_status' => 'TK/1',
                'bpjs_dependents_count' => 1,
                'bpjs_tk_enabled' => '1',
                'bpjs_tk_number' => 'TK-1122334455',
                'bpjs_kes_enabled' => '1',
                'bpjs_kes_number' => 'KES-9988776655',
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '142001928374',
                'whatsapp_number' => '081399887766',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'employees']));
        $response->assertSessionHas('success');

        $user = User::where('email', 'rian@coocabakery.com')->firstOrFail();

        $this->assertDatabaseHas('business_users', [
            'business_id' => $this->business->id,
            'user_id' => $user->id,
            'job_title' => 'Junior Baker',
            'nik_ktp' => '3271019988770002',
            'npwp' => '08.987.654.3-002.000',
            'bpjs_tk_enabled' => 1,
            'bpjs_tk_number' => 'TK-1122334455',
            'bpjs_kes_enabled' => 1,
            'bpjs_kes_number' => 'KES-9988776655',
            'bpjs_dependents_count' => 1,
            'tax_ptkp_status' => 'TK/1',
        ]);
    }

    /**
     * Test updating employee with new BPJS details and dependents.
     */
    public function test_owner_can_update_employee_bpjs_and_dependents(): void
    {
        $role = Role::where('business_id', $this->business->id)->firstOrFail();

        $response = $this->actingAs($this->ownerUser)
            ->put(route('hrm.employees.update', $this->employeeMembership->id), [
                'name' => 'Dewi Barista Updated',
                'role_id' => $role->id,
                'job_title' => 'Head Barista',
                'employment_type' => 'permanent',
                'nik_ktp' => '3271012345670009',
                'npwp' => '09.123.456.7-999.000',
                'attendance_mode' => 'geofenced',
                'primary_location_id' => $this->primaryLocation->id,
                'base_salary' => 6000000,
                'fixed_allowances' => 750000,
                'variable_allowances' => 400000,
                'tax_ptkp_status' => 'K/3',
                'bpjs_dependents_count' => 3,
                'bpjs_tk_enabled' => '1',
                'bpjs_tk_number' => 'TK-REVISED-999',
                'bpjs_kes_enabled' => '1',
                'bpjs_kes_number' => 'KES-REVISED-999',
                'bank_name' => 'BCA',
                'bank_account_number' => '8820192837',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'employees']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('business_users', [
            'id' => $this->employeeMembership->id,
            'job_title' => 'Head Barista',
            'bpjs_dependents_count' => 3,
            'bpjs_tk_number' => 'TK-REVISED-999',
            'bpjs_kes_number' => 'KES-REVISED-999',
            'tax_ptkp_status' => 'K/3',
        ]);
    }

    /**
     * Test biometric face enrollment on HRM route with AES-256 encryption.
     */
    public function test_owner_can_register_employee_face_biometrics(): void
    {
        // 1x1 transparent PNG data URI as dummy photo frame
        $dummyBase64Photo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->ownerUser)
            ->post(route('hrm.employees.face-register', $this->employeeMembership->id), [
                'photo' => $dummyBase64Photo,
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'employees']));
        $response->assertSessionHas('success');

        $this->employeeMembership->refresh();
        $this->assertTrue($this->employeeMembership->hasFaceRegistered());
        $this->assertNotNull($this->employeeMembership->face_registered_at);
        $this->assertNotNull($this->employeeMembership->face_biometric_template);
    }

    /**
     * Test self-service biometric face enrollment from employee portal.
     */
    public function test_employee_can_register_own_face_from_portal(): void
    {
        $dummyBase64Photo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->employeeUser)
            ->postJson(route('portal.face-register'), [
                'photo' => $dummyBase64Photo,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->employeeMembership->refresh();
        $this->assertTrue($this->employeeMembership->hasFaceRegistered());
    }

    /**
     * Test office location CRUD and geofence radius settings.
     */
    public function test_owner_can_manage_office_locations_and_geofencing(): void
    {
        // 1. Create new location
        $response = $this->actingAs($this->ownerUser)
            ->post(route('hrm.locations.store'), [
                'name' => 'Outlet Kemang Cabang 2',
                'city' => 'Jakarta Selatan',
                'geofence_radius_meters' => 75,
                'address' => 'Jl. Kemang Raya No. 45',
                'latitude' => -6.273800,
                'longitude' => 106.815200,
                'is_primary' => '0',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'locations']));
        $response->assertSessionHas('success');

        $location = Location::where('name', 'Outlet Kemang Cabang 2')->firstOrFail();
        $this->assertEquals(75, $location->geofence_radius_meters);
        $this->assertEquals(-6.273800, (float) $location->latitude);
        $this->assertEquals(106.815200, (float) $location->longitude);

        // 2. Update location radius
        $updateResponse = $this->actingAs($this->ownerUser)
            ->put(route('hrm.locations.update', $location->id), [
                'name' => 'Outlet Kemang Updated',
                'city' => 'Jakarta Selatan',
                'geofence_radius_meters' => 100,
                'address' => 'Jl. Kemang Raya No. 45 Kemang Village',
                'latitude' => -6.273800,
                'longitude' => 106.815200,
                'is_primary' => '0',
                'is_active' => '1',
            ]);

        $updateResponse->assertRedirect(route('hrm.index', ['tab' => 'locations']));
        $location->refresh();
        $this->assertEquals('Outlet Kemang Updated', $location->name);
        $this->assertEquals(100, $location->geofence_radius_meters);

        // 3. Delete location
        $deleteResponse = $this->actingAs($this->ownerUser)
            ->delete(route('hrm.locations.destroy', $location->id));

        $deleteResponse->assertRedirect(route('hrm.index', ['tab' => 'locations']));
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    /**
     * Test attendance clock-in with zero permanent photo retention.
     */
    public function test_attendance_clock_in_stores_no_permanent_photo_and_validates_liveness(): void
    {
        // First enroll the face template
        $dummyBase64Photo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        app(FaceVerificationService::class)->registerFaceTemplate($this->business, $this->employeeUser, $dummyBase64Photo);

        $response = $this->actingAs($this->employeeUser)
            ->postJson(route('hrm.attendance.clock-in'), [
                'latitude' => -6.229700,
                'longitude' => 106.807400,
                'accuracy' => 10.0,
                'location_id' => $this->primaryLocation->id,
                'photo' => $dummyBase64Photo,
                'face_data' => $dummyBase64Photo,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $attendance = Attendance::where('business_id', $this->business->id)
            ->where('user_id', $this->employeeUser->id)
            ->firstOrFail();

        // Enforce Zero Permanent Photo Retention
        $this->assertNull($attendance->clock_in_photo, 'Biometric attendance must not retain selfie photos permanently');
        $this->assertTrue($attendance->face_verified);
        $this->assertNotNull($attendance->clock_in_at);
        $this->assertContains($attendance->status, [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE]);
    }

    /**
     * Test attendance correction approval workflow with visual sync.
     */
    public function test_owner_can_approve_attendance_correction_ticket(): void
    {
        $targetDate = Carbon::yesterday('Asia/Jakarta')->toDateString();

        $ticket = AttendanceCorrection::create([
            'business_id' => $this->business->id,
            'user_id' => $this->employeeUser->id,
            'target_date' => $targetDate,
            'correction_number' => 'CORR-202610-0001',
            'correction_type' => 'full_day',
            'proposed_clock_in' => '08:00:00',
            'proposed_clock_out' => '17:00:00',
            'proposed_status' => 'present',
            'reason' => 'Kendala pemadaman BTS provider pada lokasi outlet',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->ownerUser)
            ->post(route('hrm.attendance.corrections.approve', $ticket->id), [
                'review_notes' => 'Disetujui setelah konfirmasi supervisor toko',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'corrections']));
        $response->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals(AttendanceCorrection::STATUS_APPROVED, $ticket->status);
        $this->assertEquals($this->ownerUser->id, $ticket->reviewed_by);
        $this->assertNotNull($ticket->reviewed_at);

        // Check synchronized attendance record
        $this->assertDatabaseHas('attendances', [
            'business_id' => $this->business->id,
            'user_id' => $this->employeeUser->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);
    }

    /**
     * Test attendance correction rejection workflow.
     */
    public function test_owner_can_reject_attendance_correction_ticket(): void
    {
        $targetDate = Carbon::yesterday('Asia/Jakarta')->toDateString();

        $ticket = AttendanceCorrection::create([
            'business_id' => $this->business->id,
            'user_id' => $this->employeeUser->id,
            'target_date' => $targetDate,
            'correction_number' => 'CORR-202610-0002',
            'correction_type' => 'clock_in_only',
            'proposed_clock_in' => '07:30:00',
            'proposed_status' => 'present',
            'reason' => 'Lupa membawa smartphone',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->ownerUser)
            ->post(route('hrm.attendance.corrections.reject', $ticket->id), [
                'reason' => 'Tidak ada bukti kehadiran fisik dari CCTV shift pagi',
            ]);

        $response->assertRedirect(route('hrm.index', ['tab' => 'corrections']));
        $response->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals(AttendanceCorrection::STATUS_REJECTED, $ticket->status);
        $this->assertEquals($this->ownerUser->id, $ticket->reviewed_by);
        $this->assertNotNull($ticket->reviewed_at);
        $this->assertEquals('Tidak ada bukti kehadiran fisik dari CCTV shift pagi', $ticket->review_notes);
    }
}
