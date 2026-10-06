<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Business;
use App\Models\Location;
use Carbon\Carbon;
use DateTimeZone;
use InvalidArgumentException;

final class TimezoneHelper
{
    public const DEFAULT_TIMEZONE = 'Asia/Jakarta';

    /**
     * List official supported IANA timezones grouped by region.
     *
     * @return array<string, array<string, string>>
     */
    public static function supportedTimezones(): array
    {
        return [
            'Indonesia' => [
                'Asia/Jakarta'  => 'Asia/Jakarta (WIB - UTC+7)',
                'Asia/Pontianak' => 'Asia/Pontianak (WIB - UTC+7)',
                'Asia/Makassar' => 'Asia/Makassar (WITA - UTC+8)',
                'Asia/Jayapura' => 'Asia/Jayapura (WIT - UTC+9)',
            ],
            'Asia & Pasifik' => [
                'Asia/Singapore'    => 'Asia/Singapore (SGT - UTC+8)',
                'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur (MYT - UTC+8)',
                'Asia/Bangkok'      => 'Asia/Bangkok (ICT - UTC+7)',
                'Asia/Manila'       => 'Asia/Manila (PHT - UTC+8)',
                'Asia/Tokyo'        => 'Asia/Tokyo (JST - UTC+9)',
                'Asia/Seoul'        => 'Asia/Seoul (KST - UTC+9)',
                'Asia/Hong_Kong'    => 'Asia/Hong_Kong (HKT - UTC+8)',
                'Australia/Perth'   => 'Australia/Perth (AWST - UTC+8)',
                'Australia/Sydney'  => 'Australia/Sydney (AEST - UTC+10)',
                'Pacific/Auckland'  => 'Pacific/Auckland (NZST - UTC+12)',
            ],
            'Global / Lainnya' => [
                'UTC'               => 'UTC (Coordinated Universal Time)',
                'Europe/London'     => 'Europe/London (GMT / BST)',
                'Europe/Amsterdam'  => 'Europe/Amsterdam (CET / CEST)',
                'America/New_York'  => 'America/New_York (EST / EDT - UTC-5)',
                'America/Chicago'   => 'America/Chicago (CST / CDT - UTC-6)',
                'America/Los_Angeles' => 'America/Los_Angeles (PST / PDT - UTC-8)',
                'Asia/Dubai'        => 'Asia/Dubai (GST - UTC+4)',
            ],
        ];
    }

    /**
     * Common Indonesian timezones formatted for dropdown selects.
     *
     * @return array<int, array{value: string, label: string, region: string}>
     */
    public static function commonIndonesianTimezones(): array
    {
        return [
            [
                'value'  => 'Asia/Jakarta',
                'label'  => 'Asia/Jakarta',
                'region' => 'WIB - UTC+7',
            ],
            [
                'value'  => 'Asia/Pontianak',
                'label'  => 'Asia/Pontianak',
                'region' => 'WIB - UTC+7',
            ],
            [
                'value'  => 'Asia/Makassar',
                'label'  => 'Asia/Makassar',
                'region' => 'WITA - UTC+8',
            ],
            [
                'value'  => 'Asia/Jayapura',
                'label'  => 'Asia/Jayapura',
                'region' => 'WIT - UTC+9',
            ],
        ];
    }

    /**
     * Check if a timezone identifier is a valid IANA timezone.
     */
    public static function isValid(string $timezone): bool
    {
        return in_array($timezone, DateTimeZone::listIdentifiers(), true);
    }

    /**
     * Resolve timezone with deterministic inheritance:
     * 1. If Location is specified:
     *    - If location timezone_mode === 'custom' and timezone is valid, return location timezone.
     *    - If location timezone_mode === 'inherit' (or fallback), return business timezone.
     * 2. If Business is specified:
     *    - Return business timezone if valid.
     * 3. Fallback to self::DEFAULT_TIMEZONE ('Asia/Jakarta').
     */
    public static function resolve(?Business $business = null, ?Location $location = null): string
    {
        if ($location !== null) {
            $mode = $location->timezone_mode ?? 'inherit';
            if ($mode === 'custom' && ! empty($location->timezone) && self::isValid($location->timezone)) {
                return $location->timezone;
            }

            // Inherit from business
            if ($location->relationLoaded('business') && $location->business) {
                return self::resolve($location->business);
            }

            if (! empty($location->business_id)) {
                $b = Business::find($location->business_id);
                if ($b) {
                    return self::resolve($b);
                }
            }
        }

        if ($business !== null && ! empty($business->timezone) && self::isValid($business->timezone)) {
            return $business->timezone;
        }

        return self::DEFAULT_TIMEZONE;
    }

    /**
     * Resolve operating hours with deterministic inheritance:
     * 1. If Location is specified:
     *    - If operating_hours_mode === 'custom' and operating_hours is valid array, return normalized operating hours.
     *    - If operating_hours_mode === 'inherit' (or fallback), return business operating hours.
     * 2. If Business is specified:
     *    - Return business operating hours if valid array.
     * 3. Fallback to default operating hours.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function operatingHours(?Business $business = null, ?Location $location = null): array
    {
        if ($location !== null) {
            $mode = $location->operating_hours_mode ?? 'inherit';
            if ($mode === 'custom' && ! empty($location->operating_hours) && is_array($location->operating_hours)) {
                return self::normalizeOperatingHours($location->operating_hours);
            }

            if ($location->relationLoaded('business') && $location->business) {
                return self::operatingHours($location->business);
            }

            if (! empty($location->business_id)) {
                $b = Business::find($location->business_id);
                if ($b) {
                    return self::operatingHours($b);
                }
            }
        }

        if ($business !== null && ! empty($business->operating_hours) && is_array($business->operating_hours)) {
            return self::normalizeOperatingHours($business->operating_hours);
        }

        return self::defaultOperatingHours();
    }

    /**
     * Check if a business or location is open at a given time (defaults to now).
     */
    public static function isOpen(?Business $business = null, ?Location $location = null, ?Carbon $at = null): bool
    {
        $tz = self::resolve($business, $location);
        $localTime = $at ? $at->copy()->setTimezone($tz) : self::now($business, $location);
        $hours = self::operatingHours($business, $location);

        return self::isOperatingAt($hours, $localTime);
    }

    /**
     * Get current time in resolved business/location timezone.
     */
    public static function now(?Business $business = null, ?Location $location = null): Carbon
    {
        $tz = self::resolve($business, $location);
        return Carbon::now($tz);
    }

    /**
     * Get today's start in resolved business/location timezone.
     */
    public static function today(?Business $business = null, ?Location $location = null): Carbon
    {
        $tz = self::resolve($business, $location);
        return Carbon::today($tz);
    }

    /**
     * Get today's date string ('YYYY-MM-DD') in resolved business/location timezone.
     */
    public static function todayString(?Business $business = null, ?Location $location = null): string
    {
        return self::now($business, $location)->toDateString();
    }

    /**
     * Convert local datetime in given timezone to UTC for database storage.
     */
    public static function toUtc(Carbon|string $datetime, string $sourceTimezone = self::DEFAULT_TIMEZONE): Carbon
    {
        if (is_string($datetime)) {
            return Carbon::parse($datetime, $sourceTimezone)->setTimezone('UTC');
        }

        return $datetime->copy()->setTimezone('UTC');
    }

    /**
     * Convert UTC canonical datetime from database to user local timezone for display.
     */
    public static function toLocal(Carbon|string|null $utcDatetime, string $targetTimezone = self::DEFAULT_TIMEZONE): ?Carbon
    {
        if ($utcDatetime === null) {
            return null;
        }

        if (is_string($utcDatetime)) {
            return Carbon::parse($utcDatetime, 'UTC')->setTimezone($targetTimezone);
        }

        return $utcDatetime->copy()->setTimezone($targetTimezone);
    }

    /**
     * Format UTC datetime to localized string with timezone abbreviation.
     */
    public static function formatLocal(
        Carbon|string|null $utcDatetime,
        string $targetTimezone = self::DEFAULT_TIMEZONE,
        string $format = 'd M Y, H:i'
    ): string {
        $local = self::toLocal($utcDatetime, $targetTimezone);
        if ($local === null) {
            return '-';
        }

        $formatted = $local->translatedFormat($format);
        $abbr = self::abbreviation($targetTimezone);

        return "{$formatted} {$abbr}";
    }

    /**
     * Get short official timezone abbreviation (e.g. WIB, WITA, WIT, SGT, UTC).
     */
    public static function abbreviation(string $timezone = self::DEFAULT_TIMEZONE): string
    {
        return match ($timezone) {
            'Asia/Jakarta', 'Asia/Pontianak' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => (new \DateTime('now', new DateTimeZone(self::isValid($timezone) ? $timezone : self::DEFAULT_TIMEZONE)))->format('T'),
        };
    }

    /**
     * Standard day keys (lowercase English to ensure DB consistency).
     *
     * @return array<string, string>
     */
    public static function daysOfWeek(): array
    {
        return [
            'monday'    => 'Senin',
            'tuesday'   => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday'  => 'Kamis',
            'friday'    => 'Jumat',
            'saturday'  => 'Sabtu',
            'sunday'    => 'Minggu',
        ];
    }

    /**
     * Provide default standard business operating hours.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function defaultOperatingHours(): array
    {
        $days = self::daysOfWeek();
        $hours = [];

        foreach ($days as $key => $label) {
            $isOpen = $key !== 'sunday';
            $hours[$key] = [
                'day_name' => $label,
                'is_open'  => $isOpen,
                'periods'  => $isOpen ? [
                    ['start' => '08:00', 'end' => '22:00'],
                ] : [],
            ];
        }

        return $hours;
    }

    /**
     * Normalize operating hours structure into uniform 7-day format.
     * Supports both new multi-period array structure and legacy landing page format.
     *
     * @param mixed $raw
     * @return array<string, array<string, mixed>>
     */
    public static function normalizeOperatingHours(mixed $raw): array
    {
        $default = self::defaultOperatingHours();
        if (empty($raw)) {
            return $default;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            } else {
                return $default;
            }
        }

        if (! is_array($raw)) {
            return $default;
        }

        $dayAliases = [
            'senin' => 'monday', 'mon' => 'monday', 'monday' => 'monday',
            'selasa' => 'tuesday', 'tue' => 'tuesday', 'tuesday' => 'tuesday',
            'rabu' => 'wednesday', 'wed' => 'wednesday', 'wednesday' => 'wednesday',
            'kamis' => 'thursday', 'thu' => 'thursday', 'thursday' => 'thursday',
            'jumat' => 'friday', 'fri' => 'friday', 'friday' => 'friday',
            'sabtu' => 'saturday', 'sat' => 'saturday', 'saturday' => 'saturday',
            'minggu' => 'sunday', 'sun' => 'sunday', 'sunday' => 'sunday',
        ];

        $normalized = $default;

        // If raw is indexed array like [{"day": "Senin", "hours": "08:00 - 17:00", "is_open": true}]
        if (array_is_list($raw)) {
            foreach ($raw as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $dayStr = strtolower(trim((string) ($item['day'] ?? '')));
                $dayKey = $dayAliases[$dayStr] ?? null;
                if (! $dayKey) {
                    continue;
                }

                $isOpen = ! empty($item['is_open']);
                $periods = [];

                if (! empty($item['periods']) && is_array($item['periods'])) {
                    foreach ($item['periods'] as $p) {
                        if (! empty($p['start']) && ! empty($p['end'])) {
                            $periods[] = [
                                'start' => trim((string) $p['start']),
                                'end'   => trim((string) $p['end']),
                            ];
                        }
                    }
                } elseif (! empty($item['hours']) && is_string($item['hours'])) {
                    // Parse legacy string e.g. "08:00 - 22:00"
                    $parts = explode('-', $item['hours']);
                    if (count($parts) === 2) {
                        $periods[] = [
                            'start' => trim($parts[0]),
                            'end'   => trim($parts[1]),
                        ];
                    }
                }

                $normalized[$dayKey] = [
                    'day_name' => self::daysOfWeek()[$dayKey],
                    'is_open'  => $isOpen,
                    'periods'  => $periods,
                ];
            }

            return $normalized;
        }

        // If raw is associative array [ 'monday' => [...], ... ]
        foreach (self::daysOfWeek() as $dayKey => $dayLabel) {
            if (isset($raw[$dayKey]) && is_array($raw[$dayKey])) {
                $entry = $raw[$dayKey];
                $isOpen = ! empty($entry['is_open']);
                $periods = [];

                if (! empty($entry['periods']) && is_array($entry['periods'])) {
                    foreach ($entry['periods'] as $p) {
                        if (! empty($p['start']) && ! empty($p['end'])) {
                            $periods[] = [
                                'start' => trim((string) $p['start']),
                                'end'   => trim((string) $p['end']),
                            ];
                        }
                    }
                } elseif (! empty($entry['hours']) && is_string($entry['hours'])) {
                    $parts = explode('-', $entry['hours']);
                    if (count($parts) === 2) {
                        $periods[] = [
                            'start' => trim($parts[0]),
                            'end'   => trim($parts[1]),
                        ];
                    }
                }

                $normalized[$dayKey] = [
                    'day_name' => $dayLabel,
                    'is_open'  => $isOpen,
                    'periods'  => $periods,
                ];
            }
        }

        return $normalized;
    }

    /**
     * Check if business or outlet is open at a given local time.
     * Supports split shifts (e.g. 10:00-14:00 and 17:00-22:00)
     * and overnight shifts across midnight (e.g. 22:00-02:00).
     */
    public static function isOperatingAt(array $operatingHours, Carbon $localTime): bool
    {
        $normalized = self::normalizeOperatingHours($operatingHours);
        $dayKey = strtolower($localTime->format('l')); // monday, tuesday, etc.
        $currentTimeStr = $localTime->format('H:i');

        // 1. Check today's scheduled periods
        $todaySchedule = $normalized[$dayKey] ?? null;
        if ($todaySchedule && ! empty($todaySchedule['is_open'])) {
            foreach ($todaySchedule['periods'] as $period) {
                $start = $period['start'] ?? '';
                $end = $period['end'] ?? '';

                if (empty($start) || empty($end)) {
                    continue;
                }

                // Standard same-day shift (e.g. 08:00 - 22:00)
                if ($start <= $end) {
                    if ($currentTimeStr >= $start && $currentTimeStr <= $end) {
                        return true;
                    }
                } else {
                    // Overnight shift starting today (e.g. 22:00 - 02:00), currently before midnight
                    if ($currentTimeStr >= $start) {
                        return true;
                    }
                }
            }
        }

        // 2. Check yesterday's overnight shifts that spill over into today
        $yesterdayKey = strtolower($localTime->copy()->subDay()->format('l'));
        $yesterdaySchedule = $normalized[$yesterdayKey] ?? null;

        if ($yesterdaySchedule && ! empty($yesterdaySchedule['is_open'])) {
            foreach ($yesterdaySchedule['periods'] as $period) {
                $start = $period['start'] ?? '';
                $end = $period['end'] ?? '';

                if (empty($start) || empty($end)) {
                    continue;
                }

                // If yesterday had overnight period (end < start), e.g. 22:00 - 02:00
                if ($end < $start) {
                    // Current time is between 00:00 and end
                    if ($currentTimeStr <= $end) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
