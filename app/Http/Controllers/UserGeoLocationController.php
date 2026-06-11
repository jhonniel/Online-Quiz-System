<?php

namespace App\Http\Controllers;

use App\Support\UserGeoLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserGeoLocationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0|max:100000',
            'context' => 'nullable|string|max:32',
        ]);

        UserGeoLocationService::record(
            $request->user(),
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            isset($validated['accuracy']) ? (float) $validated['accuracy'] : null,
            'browser',
            (string) ($validated['context'] ?? 'session'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Location saved.',
        ]);
    }
}
