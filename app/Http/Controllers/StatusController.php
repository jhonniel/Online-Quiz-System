<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function updateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:online,away,idle,offline'
        ]);

        $user = auth()->user();
        $user->updateStatus($request->status);

        return response()->json([
            'success' => true,
            'status' => $user->status,
            'last_activity' => $user->last_activity,
            'message' => 'Status updated successfully'
        ]);
    }

    public function getStatus(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'status' => $user->status,
            'last_activity' => $user->last_activity,
            'last_seen' => $user->last_seen,
            'status_icon' => $user->getStatusIcon(),
            'last_activity_text' => $user->getLastActivityText(),
        ]);
    }

    public function getOnlineUsers(): JsonResponse
    {
        $onlineUsers = \App\Models\User::where('role', 'user')
            ->where('status', 'online')
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get(['id', 'name', 'status', 'last_activity', 'last_seen']);

        return response()->json($onlineUsers);
    }

    public function getAwayUsers(): JsonResponse
    {
        $awayUsers = \App\Models\User::where('role', 'user')
            ->where('status', 'away')
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get(['id', 'name', 'status', 'last_activity', 'last_seen']);

        return response()->json($awayUsers);
    }

    public function getIdleUsers(): JsonResponse
    {
        $idleUsers = \App\Models\User::where('role', 'user')
            ->where('status', 'idle')
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get(['id', 'name', 'status', 'last_activity', 'last_seen']);

        return response()->json($idleUsers);
    }

    public function getAllUserStatuses(): JsonResponse
    {
        $users = \App\Models\User::where('role', 'user')
            ->where('is_active', true)
            ->orderBy('status', 'asc')
            ->orderBy('last_activity', 'desc')
            ->get(['id', 'name', 'status', 'last_activity', 'last_seen']);

        return response()->json($users);
    }
}
