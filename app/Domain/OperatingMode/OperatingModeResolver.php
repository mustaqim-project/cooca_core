<?php

declare(strict_types=1);

namespace App\Domain\OperatingMode;

use App\Domain\System\OperatingModeService;
use App\Models\Business;

final class OperatingModeResolver
{
    /**
     * Determine if a business is operating in Solo-Owner Mode (1 user).
     */
    public static function isSoloMode(Business $business): bool
    {
        return (new OperatingModeService())->isSoloMode($business);
    }
}
