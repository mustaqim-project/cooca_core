<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

final class PosCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string'],
            'items.*.product_name' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.batch_number' => ['nullable', 'string', 'max:50'],
            'items.*.expired_date' => ['nullable', 'date'],
            'items.*.dosage_instructions' => ['nullable', 'string', 'max:255'],
            'items.*.selected_modifiers' => ['nullable', 'array'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method' => ['required', 'string'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.store_edc_terminal_id' => ['nullable', 'string', 'exists:store_edc_terminals,id'],
            'payments.*.reference_number' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'string'],
            'customer_name_guest' => ['nullable', 'string', 'max:150'],
            'order_type' => ['nullable', 'string', 'in:dine_in,takeaway,delivery'],
            'sales_channel' => ['nullable', 'string', 'in:dine_in,takeaway,gofood,grabfood,shopeefood'],
            'external_order_ref' => ['nullable', 'string', 'max:100'],
            'table_or_reference' => ['nullable', 'string', 'max:100'],
            'pos_table_id' => ['nullable', 'string'],
            'pos_table_session_id' => ['nullable', 'string'],
            'existing_order_id' => ['nullable', 'string'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'voucher_code' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
            'points_to_redeem' => ['nullable', 'integer', 'min:0'],
            'location_id' => ['nullable', 'string'],
            'pos_register_id' => ['nullable', 'string', 'exists:pos_registers,id'],
            // Industry Specific Fields
            'vehicle_license_plate' => ['nullable', 'string', 'max:30'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_mileage' => ['nullable', 'integer', 'min:0'],
            'technician_id' => ['nullable', 'uuid', 'exists:users,id'],
            'service_notes' => ['nullable', 'string', 'max:1000'],
            'laundry_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'rack_location' => ['nullable', 'string', 'max:50'],
            'estimated_completion_at' => ['nullable', 'date'],
            'laundry_status' => ['nullable', 'string', 'max:30'],
        ];
    }
}
