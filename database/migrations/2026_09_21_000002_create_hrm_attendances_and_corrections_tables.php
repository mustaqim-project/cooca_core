<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add geofence_radius_meters to locations table
        Schema::table('locations', function (Blueprint $table): void {
            if (! Schema::hasColumn('locations', 'geofence_radius_meters')) {
                $table->unsignedInteger('geofence_radius_meters')->default(50)->after('longitude');
            }
        });

        // 2. Add attendance_mode to business_users table
        Schema::table('business_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('business_users', 'attendance_mode')) {
                $table->string('attendance_mode', 20)->default('geofenced')->after('primary_location_id');
            }
        });

        // 3. Create attendances table
        if (! Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->date('date');

                // Clock-in metadata
                $table->dateTime('clock_in_at')->nullable();
                $table->decimal('clock_in_lat', 10, 7)->nullable();
                $table->decimal('clock_in_lng', 10, 7)->nullable();
                $table->unsignedInteger('clock_in_distance_meters')->nullable();
                $table->text('clock_in_address')->nullable();
                $table->string('clock_in_photo', 255)->nullable();
                $table->string('clock_in_status', 20)->default('on_time'); // 'on_time', 'late', 'free_location'
                $table->string('clock_in_notes', 255)->nullable();

                // Clock-out metadata
                $table->dateTime('clock_out_at')->nullable();
                $table->decimal('clock_out_lat', 10, 7)->nullable();
                $table->decimal('clock_out_lng', 10, 7)->nullable();
                $table->unsignedInteger('clock_out_distance_meters')->nullable();
                $table->text('clock_out_address')->nullable();
                $table->string('clock_out_photo', 255)->nullable();
                $table->string('clock_out_status', 20)->default('normal'); // 'normal', 'early_leave', 'overtime', 'free_location'
                $table->string('clock_out_notes', 255)->nullable();

                // Durations & Calculations
                $table->unsignedInteger('work_duration_minutes')->default(0);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_leave_minutes')->default(0);
                $table->unsignedInteger('overtime_minutes')->default(0);

                // Overall presence status
                $table->string('status', 20)->default('present'); // 'present', 'late', 'half_day', 'absent', 'leave', 'sick'
                $table->boolean('is_geofenced')->default(true);
                $table->boolean('is_corrected')->default(false);
                $table->uuid('attendance_correction_id')->nullable();

                $table->timestamps();

                $table->unique(['business_id', 'user_id', 'date'], 'uk_business_user_date');
                $table->index(['business_id', 'date', 'status'], 'idx_att_lookup');
            });
        } else {
            Schema::table('attendances', function (Blueprint $table): void {
                if (! Schema::hasColumn('attendances', 'is_geofenced')) {
                    $table->boolean('is_geofenced')->default(true)->after('status');
                }
            });
        }

        // 4. Create attendance_corrections table
        if (! Schema::hasTable('attendance_corrections')) {
            Schema::create('attendance_corrections', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
                $table->string('correction_number', 64);
                $table->date('target_date');

                $table->string('correction_type', 30); // 'clock_in_only', 'clock_out_only', 'full_day', 'status_only'
                $table->time('proposed_clock_in')->nullable();
                $table->time('proposed_clock_out')->nullable();
                $table->string('proposed_status', 20)->default('present');

                $table->text('reason');
                $table->string('attachment_path', 255)->nullable();

                $table->string('status', 20)->default('pending'); // 'pending', 'approved', 'rejected'
                $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('reviewed_at')->nullable();
                $table->text('review_notes')->nullable();

                $table->timestamps();

                $table->unique(['business_id', 'correction_number'], 'uk_business_cor_number');
                $table->index(['business_id', 'status'], 'idx_att_cor_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('attendances');

        Schema::table('business_users', function (Blueprint $table): void {
            if (Schema::hasColumn('business_users', 'attendance_mode')) {
                $table->dropColumn('attendance_mode');
            }
        });

        Schema::table('locations', function (Blueprint $table): void {
            if (Schema::hasColumn('locations', 'geofence_radius_meters')) {
                $table->dropColumn('geofence_radius_meters');
            }
        });
    }
};
