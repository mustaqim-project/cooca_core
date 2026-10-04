<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

final class PosOpenShiftRequest extends FormRequest
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
            'opening_cash' => ['nullable', 'numeric', 'min:0'],
            'location_id' => ['nullable', 'string', 'exists:locations,id'],
            'pos_register_id' => ['nullable', 'string', 'exists:pos_registers,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'opening_denominations' => ['nullable', 'array'],
        ];
    }
}
