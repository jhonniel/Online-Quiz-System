<?php

namespace App\Http\Controllers;

use App\Models\ChatMessageMedia;
use App\Support\ChatMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatMediaController extends Controller
{
    public function show(Request $request, ChatMessageMedia $chatMessageMedia)
    {
        return ChatMediaService::streamForViewer($chatMessageMedia, $request->user());
    }

    public function markViewed(Request $request, ChatMessageMedia $chatMessageMedia): JsonResponse
    {
        ChatMediaService::markViewed($chatMessageMedia, $request->user());

        return response()->json([
            'success' => true,
            'media' => ChatMediaService::serializeForViewer(
                $chatMessageMedia->fresh(['views']),
                $request->user()
            ),
        ]);
    }
}
