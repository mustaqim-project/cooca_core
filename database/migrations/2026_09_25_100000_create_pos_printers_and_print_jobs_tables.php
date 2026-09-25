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
        Schema::create('pos_printers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('name', 100);
            $table->string('connection_type', 30)->default('lan'); // lan, wifi, usb, bluetooth, windows, serial, agent
            $table->string('interface_address', 255); // IP address, Windows printer name, COM port, or USB device path
            $table->unsignedInteger('port')->default(9100);
            $table->string('paper_width', 10)->default('80mm'); // 58mm, 80mm
            $table->string('character_set', 50)->default('CP437');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('capabilities')->nullable(); // ["print_text","print_image","barcode","qr_code","cut","cash_drawer","open_drawer","beep"]
            $table->json('assigned_usages')->nullable(); // ["cashier_receipt","kitchen_order","bar_order","shift_report","label"]
            $table->json('assigned_category_ids')->nullable(); // Category IDs routed to this printer (for kitchen/bar)
            $table->string('last_status', 30)->default('unknown'); // online, offline, error, unknown
            $table->timestamp('last_status_checked_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'location_id']);
            $table->index(['business_id', 'is_active']);
            $table->index(['business_id', 'connection_type']);
        });

        Schema::create('pos_print_jobs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignUuid('printer_id')->nullable()->constrained('pos_printers')->nullOnDelete();
            $table->string('document_type', 50); // receipt, kitchen_order, bar_order, shift_report, test_print, cash_drawer_pulse
            $table->string('document_id', 64)->nullable(); // e.g. pos_order_id, pos_shift_id
            $table->longText('payload_raw')->nullable(); // Base64 encoded or JSON serialized ESC/POS command stream
            $table->string('status', 30)->default('pending'); // pending, processing, printed, failed, cancelled
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'document_type']);
            $table->index(['business_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_print_jobs');
        Schema::dropIfExists('pos_printers');
    }
};
