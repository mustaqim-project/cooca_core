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
     *     biteship_area_id: string,
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
            ])->timeout(8)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $latitude,
                'lon' => $longitude,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $addr = $data['address'] ?? [];
                $displayName = (string) ($data['display_name'] ?? '');
                $parsed = $this->parseNominatimAddress($addr, $displayName);

                $biteshipAreaId = '';
                if (! empty($parsed['postal_code'])) {
                    try {
                        $biteshipAreas = $this->searchAreas($parsed['postal_code']);
                        if (! empty($biteshipAreas)) {
                            $bestMatch = null;
                            foreach ($biteshipAreas as $bArea) {
                                if (! empty($parsed['district']) && stripos($bArea['district'], $parsed['district']) !== false) {
                                    $bestMatch = $bArea;
                                    break;
                                }
                                if (! empty($parsed['village']) && stripos($bArea['village'], $parsed['village']) !== false) {
                                    $bestMatch = $bArea;
                                    break;
                                }
                            }
                            if (! $bestMatch) {
                                $bestMatch = $biteshipAreas[0];
                            }

                            $biteshipAreaId = (string) ($bestMatch['id'] ?? '');
                            if (! empty($bestMatch['province'])) {
                                $parsed['province'] = $bestMatch['province'];
                            }
                            if (! empty($bestMatch['city'])) {
                                $parsed['city'] = $bestMatch['city'];
                            }
                            if (! empty($bestMatch['district'])) {
                                $parsed['district'] = $bestMatch['district'];
                            }
                            if (! empty($bestMatch['village'])) {
                                $parsed['village'] = $bestMatch['village'];
                            }
                        }
                    } catch (Throwable) {
                        // Pertahankan parsed Nominatim jika pencarian Biteship mengalami kendala jaringan
                    }
                }

                return [
                    'success' => true,
                    'road' => $parsed['road'],
                    'village' => $parsed['village'],
                    'district' => $parsed['district'],
                    'city' => $parsed['city'],
                    'province' => $parsed['province'],
                    'postal_code' => $parsed['postal_code'],
                    'biteship_area_id' => $biteshipAreaId,
                    'display_name' => $displayName,
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
            'biteship_area_id' => '',
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
     * @param string $displayName
     * @return array{
     *     village: string,
     *     district: string,
     *     city: string,
     *     province: string,
     *     postal_code: string,
     *     road: string
     * }
     */
    private function parseNominatimAddress(array $addr, string $displayName = ''): array
    {
        $road = (string) ($addr['road'] ?? $addr['pedestrian'] ?? $addr['street'] ?? $addr['path'] ?? '');
        $rawPostcode = (string) ($addr['postcode'] ?? '');
        $postalCode = preg_replace('/\D+/', '', $rawPostcode) ?? '';
        if (strlen($postalCode) > 5) {
            $postalCode = substr($postalCode, 0, 5);
        }

        // 1. Deteksi Provinsi
        $rawState = (string) ($addr['state'] ?? '');
        $rawCity = (string) ($addr['city'] ?? '');
        $rawCityDistrict = (string) ($addr['city_district'] ?? '');
        $iso = (string) ($addr['ISO3166-2-lvl4'] ?? '');

        $province = $rawState;
        if ($iso === 'ID-JK' || stripos($rawCity, 'Jakarta') !== false || stripos($rawState, 'Jakarta') !== false || stripos($displayName, 'Daerah Khusus Ibukota Jakarta') !== false || stripos($displayName, 'DKI Jakarta') !== false) {
            $province = 'DKI Jakarta';
        } elseif ($iso === 'ID-YO' || stripos($rawState, 'Yogyakarta') !== false || stripos($displayName, 'Daerah Istimewa Yogyakarta') !== false) {
            $province = 'Daerah Istimewa Yogyakarta';
        } elseif (empty($province)) {
            $province = (string) ($addr['region'] ?? ($addr['province'] ?? ''));
        }

        // 2. Deteksi Kota / Kabupaten
        $city = '';
        if ($province === 'DKI Jakarta') {
            // Dalam DKI Jakarta, city_district (misal Jakarta Barat, Jakarta Selatan, dll) adalah Kota Administrasinya
            if (! empty($rawCityDistrict) && stripos($rawCityDistrict, 'Jakarta') !== false) {
                $city = $rawCityDistrict;
            } elseif (! empty($rawCity) && stripos($rawCity, 'Jakarta') !== false && ! in_array($rawCity, ['DKI Jakarta', 'Daerah Khusus Ibukota Jakarta'], true)) {
                $city = $rawCity;
            } elseif (! empty($displayName) && preg_match('/(Jakarta\s+[A-Za-z]+)/i', $displayName, $m)) {
                $city = $m[1];
            } else {
                $city = 'Jakarta Selatan';
            }
        } else {
            $city = $rawCity ?: ($addr['town'] ?? ($addr['county'] ?? ($addr['regency'] ?? '')));
            if (empty($city) && ! empty($rawCityDistrict)) {
                $city = $rawCityDistrict;
            }
        }

        // 3. Deteksi Kecamatan
        $district = (string) (
            $addr['district'] 
            ?? $addr['municipality'] 
            ?? $addr['subdistrict'] 
            ?? ''
        );

        if (empty($district) && ! empty($rawCityDistrict) && $rawCityDistrict !== $city) {
            $district = $rawCityDistrict;
        }

        // 4. Deteksi Kelurahan / Desa
        $cleanNeighbourhood = (string) ($addr['neighbourhood'] ?? '');
        if (preg_match('/^(RT|RW|\d+)/i', trim($cleanNeighbourhood))) {
            $cleanNeighbourhood = '';
        }

        $village = (string) (
            $addr['village'] 
            ?? ($cleanNeighbourhood ?: ($addr['quarter'] ?? ($addr['hamlet'] ?? '')))
        );

        if (empty($village) && ! empty($addr['suburb']) && $addr['suburb'] !== $district) {
            $village = (string) $addr['suburb'];
        }

        if (empty($district) && ! empty($addr['suburb']) && $addr['suburb'] !== $village) {
            $district = (string) $addr['suburb'];
        }

        // Ekstraksi fallback dari display_name jika kecamatan masih kosong
        if (empty($district) && ! empty($displayName)) {
            $parts = array_map('trim', explode(',', $displayName));
            foreach ($parts as $idx => $part) {
                if ($part === $city || stripos($part, $city) !== false) {
                    if (isset($parts[$idx - 1]) && $parts[$idx - 1] !== $village) {
                        $candidate = $parts[$idx - 1];
                        if (! preg_match('/^(RT|RW|\d+)/i', $candidate)) {
                            $district = $candidate;
                        }
                    }
                    break;
                }
            }
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
