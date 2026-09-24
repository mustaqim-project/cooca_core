<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use App\Domain\Shipping\BiteshipService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GeoLocationService
{
    private BiteshipService $biteshipService;

    public function __construct(?BiteshipService $biteshipService = null)
    {
        $this->biteshipService = $biteshipService ?? app(BiteshipService::class);
    }

    /**
     * Cari area wilayah Indonesia berdasarkan Nama Desa/Kelurahan atau Kode Pos.
     *
     * @return array<int, array{
     *     id: string,
     *     label: string,
     *     village: string,
     *     district: string,
     *     city: string,
     *     province: string,
     *     postal_code: string,
     *     latitude: float|null,
     *     longitude: float|null
     * }>
     */
    public function searchAreas(string $query): array
    {
        $query = trim($query);
        if (strlen($query) < 3) {
            return [];
        }

        // 1. Coba pencarian via Biteship Maps API resmi logistik Indonesia
        $biteshipResults = $this->searchViaBiteship($query);
        if (! empty($biteshipResults)) {
            return $biteshipResults;
        }

        // 2. Fallback via OpenStreetMap Nominatim
        return $this->searchViaNominatim($query);
    }

    /**
     * Reverse Geocoding: Dapatkan informasi wilayah dari koordinat GPS latitude/longitude.
     *
     * @return array{
     *     success: bool,
     *     road: string,
     *     village: string,
     *     district: string,
     *     city: string,
     *     province: string,
     *     postal_code: string,
     *     display_name: string,
     *     latitude: float,
     *     longitude: float
     * }
     */
    public function reverseGeocode(float $latitude, float $longitude): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Cooca-ERP-App/1.0 (support@cooca.id)',
                'Accept-Language' => 'id-ID,id;q=0.9',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $latitude,
                'lon' => $longitude,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $addr = $data['address'] ?? [];
                $parsed = $this->parseNominatimAddress($addr);

                return [
                    'success' => true,
                    'road' => $parsed['road'],
                    'village' => $parsed['village'],
                    'district' => $parsed['district'],
                    'city' => $parsed['city'],
                    'province' => $parsed['province'],
                    'postal_code' => $parsed['postal_code'],
                    'display_name' => (string) ($data['display_name'] ?? ''),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            }
        } catch (Throwable $e) {
            Log::warning('[GeoLocationService::reverseGeocode] Error: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'road' => '',
            'village' => '',
            'district' => '',
            'city' => '',
            'province' => '',
            'postal_code' => '',
            'display_name' => '',
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * Pencarian via Biteship Maps Areas API.
     *
     * @return array<int, array{
     *     id: string,
     *     label: string,
     *     village: string,
     *     district: string,
     *     city: string,
     *     province: string,
     *     postal_code: string,
     *     latitude: float|null,
     *     longitude: float|null
     * }>
     */
    private function searchViaBiteship(string $query): array
    {
        $apiKey = $this->biteshipService->getApiKey();
        if (empty($apiKey)) {
            return [];
        }

        try {
            $baseUrl = config('services.biteship.base_url', 'https://api.biteship.com');
            $response = Http::withHeaders([
                'authorization' => $apiKey,
                'content-type' => 'application/json',
            ])->timeout(6)->get("{$baseUrl}/v1/maps/areas", [
                'countries' => 'ID',
                'input' => $query,
                'type' => 'single',
            ]);

            if ($response->successful()) {
                $areas = $response->json('areas') ?? [];
                $results = [];

                foreach ($areas as $item) {
                    $village = (string) ($item['administrative_division_level_4_name'] ?? '');
                    $district = (string) ($item['administrative_division_level_3_name'] ?? '');
                    $city = (string) ($item['administrative_division_level_2_name'] ?? '');
                    $province = (string) ($item['administrative_division_level_1_name'] ?? '');
                    $postalCode = (string) ($item['postal_code'] ?? '');

                    $parts = array_filter([$village, $district, $city, $province]);
                    $label = implode(', ', $parts) . ($postalCode ? " ({$postalCode})" : '');

                    $lat = isset($item['latitude']) && is_numeric($item['latitude']) ? (float) $item['latitude'] : null;
                    $lng = isset($item['longitude']) && is_numeric($item['longitude']) ? (float) $item['longitude'] : null;

                    $results[] = [
                        'id' => (string) ($item['id'] ?? ''),
                        'label' => $label,
                        'village' => $village,
                        'district' => $district,
                        'city' => $city,
                        'province' => $province,
                        'postal_code' => $postalCode,
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ];
                }

                return $results;
            }
        } catch (Throwable $e) {
            Log::warning('[GeoLocationService::searchViaBiteship] Warning: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Pencarian via OpenStreetMap Nominatim API.
     *
     * @return array<int, array{
     *     id: string,
     *     label: string,
     *     village: string,
     *     district: string,
     *     city: string,
     *     province: string,
     *     postal_code: string,
     *     latitude: float|null,
     *     longitude: float|null
     * }>
     */
    private function searchViaNominatim(string $query): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Cooca-ERP-App/1.0 (support@cooca.id)',
                'Accept-Language' => 'id-ID,id;q=0.9',
            ])->timeout(6)->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'json',
                'countrycodes' => 'id',
                'addressdetails' => 1,
                'limit' => 8,
                'q' => $query,
            ]);

            if ($response->successful()) {
                $items = $response->json() ?? [];
                $results = [];

                foreach ($items as $item) {
                    $addr = $item['address'] ?? [];
                    $parsed = $this->parseNominatimAddress($addr);

                    $village = $parsed['village'];
                    $district = $parsed['district'];
                    $city = $parsed['city'];
                    $province = $parsed['province'];
                    $postalCode = $parsed['postal_code'];

                    $parts = array_filter([$village ?: ($item['name'] ?? ''), $district, $city, $province]);
                    $label = implode(', ', $parts) . ($postalCode ? " ({$postalCode})" : '');

                    $results[] = [
                        'id' => (string) ($item['osm_id'] ?? ''),
                        'label' => $label ?: ($item['display_name'] ?? ''),
                        'village' => $village,
                        'district' => $district,
                        'city' => $city,
                        'province' => $province,
                        'postal_code' => $postalCode,
                        'latitude' => isset($item['lat']) ? (float) $item['lat'] : null,
                        'longitude' => isset($item['lon']) ? (float) $item['lon'] : null,
                    ];
                }

                return $results;
            }
        } catch (Throwable $e) {
            Log::warning('[GeoLocationService::searchViaNominatim] Error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Helper ekstraksi hierarki wilayah Indonesia dari struktur OpenStreetMap Nominatim.
     *
     * @param array<string, mixed> $addr
     * @return array{
     *     village: string,
     *     district: string,
     *     city: string,
     *     province: string,
     *     postal_code: string,
     *     road: string
     * }
     */
    private function parseNominatimAddress(array $addr): array
    {
        $road = (string) ($addr['road'] ?? $addr['pedestrian'] ?? $addr['street'] ?? $addr['path'] ?? '');

        $village = (string) (
            $addr['village']
            ?? $addr['neighbourhood']
            ?? $addr['quarter']
            ?? $addr['suburb']
            ?? $addr['hamlet']
            ?? $addr['residential']
            ?? ''
        );

        $district = (string) (
            $addr['municipality']
            ?? $addr['subdistrict']
            ?? $addr['district']
            ?? $addr['city_district']
            ?? $addr['county']
            ?? $addr['township']
            ?? $addr['borough']
            ?? ''
        );

        $city = (string) (
            $addr['city']
            ?? $addr['town']
            ?? $addr['regency']
            ?? $addr['state_district']
            ?? ($addr['county'] ?? '')
        );

        $province = (string) (
            $addr['state']
            ?? $addr['region']
            ?? $addr['province']
            ?? ''
        );

        $postalCode = (string) ($addr['postcode'] ?? '');

        if (empty($district) && ! empty($addr['suburb']) && $addr['suburb'] !== $village) {
            $district = (string) $addr['suburb'];
        }

        return [
            'village' => trim($village),
            'district' => trim($district),
            'city' => trim($city),
            'province' => trim($province),
            'postal_code' => trim($postalCode),
            'road' => trim($road),
        ];
    }
}
