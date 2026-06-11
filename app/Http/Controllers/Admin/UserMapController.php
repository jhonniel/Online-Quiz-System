<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TomTomService;
use App\Support\UserMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserMapController extends Controller
{
    public function index(): View
    {
        return view('admin.user-maps.index', [
            'hasTomTomKey' => TomTomService::hasApiKey(),
            'tomtomMapStyleUrl' => TomTomService::mapStyleUrl(),
        ]);
    }

    public function mapData(Request $request): JsonResponse
    {
        if (! TomTomService::hasApiKey()) {
            return response()->json([
                'message' => 'TomTom API key is not configured. Add it in Admin Settings.',
            ], 422);
        }

        $validated = $request->validate([
            'search' => 'nullable|string|max:120',
            'role' => 'nullable|string|max:40',
            'department_id' => 'nullable|integer|min:1',
            'university_id' => 'nullable|integer|min:1',
            'online_only' => 'nullable|in:0,1,true,false',
        ]);

        $payload = UserMapService::build([
            'search' => $validated['search'] ?? null,
            'role' => $validated['role'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'university_id' => $validated['university_id'] ?? null,
            'online_only' => filter_var($request->input('online_only', false), FILTER_VALIDATE_BOOLEAN),
        ]);

        return response()->json($payload);
    }
}
