<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Common;

use App\Domain\Shared\GeoLocationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GeoLocationController extends Controller
{
    private GeoLocationService $geoService;

    public function __construct(GeoLocationService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * Cari area wilayah Indonesia berdasarkan Nama Kelurahan/Desa atau Kode Pos.
     */
    public function searchAreas(Request $request): JsonResponse
    {
        $query = (string) $request->query('query', '');
        if (strlen(trim($query)) < 2) {
            return response()->json([
                'success' => true,
                'areas' => [],
            ]);
        }

        $areas = $this->geoService->searchAreas($query);

        return response()->json([
            'success' => true,
            'areas' => $areas,
        ]);
    }

    /**
     * Reverse geocoding titik koordinat GPS Leaflet ke nama wilayah.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');

        $result = $this->geoService->reverseGeocode($lat, $lng);

        return response()->json($result);
    }
}
