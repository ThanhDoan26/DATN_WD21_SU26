<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Set user's selected location in session.
     */
    public function setLocation(Request $request): JsonResponse|RedirectResponse
    {
        $city = $request->input('city');

        // Sanitize input
        if (empty($city) || strtoupper(trim($city)) === 'ALL' || trim($city) === 'Toàn quốc') {
            session()->forget('user_location');
            $selectedLocation = 'ALL';
            $locationLabel = 'Toàn quốc';
        } else {
            $city = trim($city);
            session(['user_location' => $city]);
            $selectedLocation = $city;
            $locationLabel = $city;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'location' => $selectedLocation,
                'label' => $locationLabel,
                'message' => 'Đã cập nhật vị trí thành công: ' . $locationLabel,
            ]);
        }

        return back()->with('success', 'Đã chuyển vị trí xem phim sang: ' . $locationLabel);
    }

    /**
     * Quick switch location by URL parameter.
     */
    public function switchLocation(string $city): RedirectResponse
    {
        if (strtoupper($city) === 'ALL' || $city === 'all' || $city === 'toan-quoc') {
            session()->forget('user_location');
            return back()->with('info', 'Đã chuyển về xem phim Toàn quốc.');
        }

        $decodedCity = urldecode($city);
        session(['user_location' => $decodedCity]);

        return back()->with('success', "Đã chuyển vị trí xem phim sang {$decodedCity}.");
    }

    /**
     * Get list of active cinema cities and all provinces.
     */
    public function getLocations(): JsonResponse
    {
        $activeCinemas = Cinema::where('status', 'ACTIVE')
            ->whereNotNull('city')
            ->select('id', 'name', 'city')
            ->get();

        $cinemaCities = $activeCinemas->groupBy('city')->map(function ($group, $cityName) {
            return [
                'city' => $cityName,
                'cinemas_count' => $group->count(),
            ];
        })->values();

        $allProvinces = config('provinces', []);

        return response()->json([
            'current' => session('user_location', 'ALL'),
            'cinema_cities' => $cinemaCities,
            'all_provinces' => $allProvinces,
        ]);
    }
}
