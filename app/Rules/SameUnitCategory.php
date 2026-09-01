<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Unit;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class SameUnitCategory implements ValidationRule
{
    public function __construct(private readonly string $targetUnitId) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $unitA = Unit::available()->find($value);
        $unitB = Unit::available()->find($this->targetUnitId);

        if ($unitA === null || $unitB === null) {
            $fail("One or both units specified for [{$attribute}] are invalid.");

            return;
        }

        if ($unitA->category !== $unitB->category && $unitA->category !== Unit::CATEGORY_CUSTOM && $unitB->category !== Unit::CATEGORY_CUSTOM) {
            $fail("The unit [{$unitA->code}] ({$unitA->category}) is incompatible with [{$unitB->code}] ({$unitB->category}).");
        }
    }
}
