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
        // 1. Add face biometric template & registration timestamp to business_users table
        Schema::table('business_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('business_users', 'face_biometric_template')) {
                $table->longText('face_biometric_template')->nullable()->after('pin_hash');
            }
            if (! Schema::hasColumn('business_users', 'face_registered_at')) {
                $table->timestamp('face_registered_at')->nullable()->after('face_biometric_template');
            }
        });

        // 2. Add face verification audit flags to attendances table
        Schema::table('attendances', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendances', 'face_verified')) {
                $table->boolean('face_verified')->default(false)->after('is_geofenced');
            }
            if (! Schema::hasColumn('attendances', 'face_similarity_score')) {
                $table->decimal('face_similarity_score', 5, 2)->nullable()->after('face_verified');
            }
            if (! Schema::hasColumn('attendances', 'exception_policy_id')) {
                $table->uuid('exception_policy_id')->nullable()->after('face_similarity_score');
            }
        });

        // 3. Create attendance_exceptions table
        if (! Schema::hasTable('attendance_exceptions')) {
            Schema::create('attendance_exceptions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                
                // Exception mode: 'wfh', 'wfa', 'field_work', 'business_trip', 'temporary_assignment'
                $table->string('exception_mode', 30)->default('wfh');
                $table->date('start_date');
                $table->date('end_date');
                
                // Specific permitted location coordinates (optional for WFH/field, null for WFA)
                $table->decimal('allowed_latitude', 10, 7)->nullable();
                $table->decimal('allowed_longitude', 10, 7)->nullable();
                $table->unsignedInteger('allowed_radius_meters')->default(100);
                $table->string('location_name', 150)->nullable();
                
                $table->text('reason');
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('approved'); // 'pending', 'approved', 'rejected', 'revoked'
                
                $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('approved_at')->nullable();
                
                $table->timestamps();

                $table->index(['business_id', 'user_id', 'status'], 'idx_att_exc_lookup');
                $table->index(['business_id', 'start_date', 'end_date'], 'idx_att_exc_dates');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_exceptions');

        Schema::table('attendances', function (Blueprint $table): void {
            if (Schema::hasColumn('attendances', 'exception_policy_id')) {
                $table->dropColumn('exception_policy_id');
            }
            if (Schema::hasColumn('attendances', 'face_similarity_score')) {
                $table->dropColumn('face_similarity_score');
            }
            if (Schema::hasColumn('attendances', 'face_verified')) {
                $table->dropColumn('face_verified');
            }
        });

        Schema::table('business_users', function (Blueprint $table): void {
            if (Schema::hasColumn('business_users', 'face_registered_at')) {
                $table->dropColumn('face_registered_at');
            }
            if (Schema::hasColumn('business_users', 'face_biometric_template')) {
                $table->dropColumn('face_biometric_template');
            }
        });
    }
};
