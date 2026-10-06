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
        // 1. Create work_shifts table
        if (! Schema::hasTable('work_shifts')) {
            Schema::create('work_shifts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->string('name', 100);
                $table->string('code', 20)->nullable();
                $table->string('start_time', 5); // '08:00'
                $table->string('end_time', 5);   // '16:00' or '06:00'
                $table->unsignedInteger('break_duration_minutes')->default(0);
                $table->unsignedInteger('grace_period_minutes')->default(0);
                $table->boolean('is_overnight')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('color', 20)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'is_active']);
                $table->index(['business_id', 'location_id']);
            });
        }

        // 2. Create employee_schedules table
        if (! Schema::hasTable('employee_schedules')) {
            Schema::create('employee_schedules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->foreignUuid('work_shift_id')->nullable()->constrained('work_shifts')->nullOnDelete();
                $table->string('schedule_type', 20)->default('recurring'); // 'recurring', 'specific_date'
                $table->string('day_of_week', 15)->nullable(); // 'monday', 'tuesday', etc.
                $table->date('specific_date')->nullable();
                $table->boolean('is_off_day')->default(false);
                $table->date('effective_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->index(['business_id', 'user_id', 'schedule_type']);
                $table->index(['business_id', 'user_id', 'day_of_week']);
                $table->index(['business_id', 'user_id', 'specific_date']);
            });
        }

        // 3. Add shift snapshot & precision columns to attendances table
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table): void {
                if (! Schema::hasColumn('attendances', 'work_shift_id')) {
                    $table->foreignUuid('work_shift_id')->nullable()->after('location_id')->constrained('work_shifts')->nullOnDelete();
                }
                if (! Schema::hasColumn('attendances', 'shift_name')) {
                    $table->string('shift_name', 100)->nullable()->after('work_shift_id');
                }
                if (! Schema::hasColumn('attendances', 'scheduled_start_at')) {
                    $table->dateTime('scheduled_start_at')->nullable()->after('shift_name');
                }
                if (! Schema::hasColumn('attendances', 'scheduled_end_at')) {
                    $table->dateTime('scheduled_end_at')->nullable()->after('scheduled_start_at');
                }
                if (! Schema::hasColumn('attendances', 'early_in_minutes')) {
                    $table->unsignedInteger('early_in_minutes')->default(0)->after('work_duration_minutes');
                }
                if (! Schema::hasColumn('attendances', 'timezone')) {
                    $table->string('timezone', 50)->nullable()->after('early_leave_minutes');
                }
            });
        }

        // 4. Add default_shift_id to business_users table
        if (Schema::hasTable('business_users')) {
            Schema::table('business_users', function (Blueprint $table): void {
                if (! Schema::hasColumn('business_users', 'default_shift_id')) {
                    $table->foreignUuid('default_shift_id')->nullable()->after('primary_location_id')->constrained('work_shifts')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('business_users')) {
            Schema::table('business_users', function (Blueprint $table): void {
                if (Schema::hasColumn('business_users', 'default_shift_id')) {
                    $table->dropForeign(['default_shift_id']);
                    $table->dropColumn('default_shift_id');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table): void {
                if (Schema::hasColumn('attendances', 'work_shift_id')) {
                    $table->dropForeign(['work_shift_id']);
                    $table->dropColumn('work_shift_id');
                }
                $columns = ['shift_name', 'scheduled_start_at', 'scheduled_end_at', 'early_in_minutes', 'timezone'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('attendances', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('employee_schedules');
        Schema::dropIfExists('work_shifts');
    }
};
