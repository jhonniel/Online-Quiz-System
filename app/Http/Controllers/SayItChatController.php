<?php

namespace App\Http\Controllers;

use App\Models\ConfessionHashtag;
use App\Models\ConfessionTopic;
use App\Models\SayItChatMessage;
use App\Models\SayItChatRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SayItChatController extends Controller
{
    public function index(Request $request): View
    {
        $codename = SayItController::codenameForSession($request);

        $rooms = SayItChatRoom::query()
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('say-it.chat.index', array_merge(
            compact('rooms', 'codename'),
            $this->sidebarData()
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        $codename = SayItController::codenameForSession($request);
        $name = trim($validated['name']);

        $room = SayItChatRoom::create([
            'name' => $name,
            'slug' => SayItChatRoom::uniqueSlugFromName($name),
            'creator_codename' => $codename,
        ]);

        return redirect()
            ->route('say-it.chat.show', $room)
            ->with('success', 'Room created. You are chatting as '.$codename.'.');
    }

    public function show(Request $request, SayItChatRoom $room): View
    {
        $codename = SayItController::codenameForSession($request);

        $initialMessages = $room->messages()
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->sortBy('id')
            ->values()
            ->map(fn (SayItChatMessage $m) => $m->toClientPayload($codename));

        return view('say-it.chat.show', array_merge(
            [
                'room' => $room,
                'codename' => $codename,
                'initialMessages' => $initialMessages,
            ],
            $this->sidebarData()
        ));
    }

    public function messages(Request $request, SayItChatRoom $room): JsonResponse
    {
        $codename = SayItController::codenameForSession($request);
        $afterId = (int) $request->query('after_id', 0);

        if ($afterId > 0) {
            $messages = $room->messages()
                ->where('id', '>', $afterId)
                ->orderBy('id')
                ->limit(50)
                ->get();
        } else {
            $messages = $room->messages()
                ->orderByDesc('id')
                ->limit(50)
                ->get()
                ->sortBy('id')
                ->values();
        }

        return response()->json([
            'messages' => $messages->map(fn (SayItChatMessage $m) => $m->toClientPayload($codename))->values(),
            'codename' => $codename,
        ]);
    }

    public function sendMessage(Request $request, SayItChatRoom $room): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $codename = SayItController::codenameForSession($request);
        $body = trim($validated['body']);

        if ($body === '') {
            return response()->json(['error' => 'Message cannot be empty.'], 422);
        }

        $message = SayItChatMessage::create([
            'sayit_chat_room_id' => $room->id,
            'codename' => $codename,
            'body' => $body,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $room->forceFill(['last_message_at' => now()])->save();

        return response()->json([
            'success' => true,
            'message' => $message->toClientPayload($codename),
        ]);
    }

    public function destroyMessage(Request $request, SayItChatRoom $room, SayItChatMessage $message): JsonResponse
    {
        if ((int) $message->sayit_chat_room_id !== (int) $room->id) {
            return response()->json(['success' => false, 'message' => 'Message not found.'], 404);
        }

        $secondsSinceCreation = now()->diffInSeconds($message->created_at);
        if ($secondsSinceCreation > 50) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your message within 50 seconds of sending.',
            ], 403);
        }

        $sessionCodename = $request->session()->get('sayit_codename');
        if ($message->codename !== $sessionCodename || $message->ip_address !== $request->ip()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own messages.',
            ], 403);
        }

        $message->delete();

        return response()->json(['success' => true, 'message' => 'Message deleted.']);
    }

    /**
     * @return array{topTopics: \Illuminate\Support\Collection, topHashtags: \Illuminate\Support\Collection}
     */
    private function sidebarData(): array
    {
        return [
            'topTopics' => ConfessionTopic::orderByDesc('posts_count')->orderBy('name')->limit(10)->get(['id', 'name', 'slug', 'posts_count']),
            'topHashtags' => ConfessionHashtag::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']),
        ];
    }
}
