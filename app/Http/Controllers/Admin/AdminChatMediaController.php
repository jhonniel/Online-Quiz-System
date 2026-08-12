<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessageMedia;
use App\Support\ChatMediaService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminChatMediaController extends Controller
{
    public function show(Request $request, ChatMessageMedia $chatMessageMedia): StreamedResponse
    {
        if (! ChatMediaService::adminCanAccessMedia($chatMessageMedia, $request->user())) {
            abort(403);
        }

        return ChatMediaService::streamForAdmin($chatMessageMedia);
    }
}
