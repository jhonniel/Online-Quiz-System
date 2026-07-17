<?php

namespace App\Http\Controllers;

use App\Helpers\SayItHelper;
use App\Models\ConfessionHashtag;
use App\Models\ConfessionTopic;
use App\Models\SayItChatMessage;
use App\Models\SayItChatRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SayItChatController extends Controller
{
    public function index(Request $request): View
    {
        SayItChatRoom::clearRoomUnlock($request);

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
            'password' => ['nullable', 'string', 'min:4', 'max:64'],
            'avatar' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpeg,jpg,png,gif,webp',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp',
            ],
        ], [
            'password.min' => 'Room password must be at least 4 characters.',
            'avatar.mimes' => 'The room profile must be an image (JPEG, PNG, GIF, or WebP).',
            'avatar.mimetypes' => 'The room profile must be an image (JPEG, PNG, GIF, or WebP).',
            'avatar.max' => 'The room profile may not be larger than 5 MB.',
        ]);

        $codename = SayItController::codenameForSession($request);
        $name = trim($validated['name']);
        $plainPassword = trim((string) ($validated['password'] ?? ''));

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $stored = $this->storeAvatarFile($request->file('avatar'));
            if ($stored instanceof RedirectResponse) {
                return $stored;
            }
            $avatarPath = $stored;
        }

        $codes = SayItChatRoom::generateModerationCodes();

        $room = new SayItChatRoom([
            'name' => $name,
            'slug' => SayItChatRoom::uniqueSlugFromName($name),
            'creator_codename' => $codename,
            'avatar_path' => $avatarPath,
            'freeze_code' => $codes['freeze'],
            'delete_code' => $codes['delete'],
            'gibberish_code' => $codes['gibberish'],
        ]);
        $room->setRoomPassword($plainPassword !== '' ? $plainPassword : null);
        $room->save();

        $room->unlockFor($request);

        $flashCodes = [
            'freeze' => $codes['freeze'],
            'delete' => $codes['delete'],
            'gibberish' => $codes['gibberish'],
        ];
        if ($plainPassword !== '') {
            $flashCodes['password'] = $plainPassword;
        }

        return redirect()
            ->route('say-it.chat.show', $room)
            ->with('success', 'Room created. You are chatting as '.$codename.'.')
            ->with('moderation_codes', $flashCodes);
    }

    public function unlock(Request $request, SayItChatRoom $room): RedirectResponse
    {
        if (! $room->hasPassword()) {
            return redirect()->route('say-it.chat.show', $room);
        }

        if ($room->isUnlockedFor($request)) {
            return redirect()->route('say-it.chat.show', $room);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'max:64'],
        ]);

        if (! $room->checkPassword($validated['password'])) {
            return back()->withErrors(['password' => 'Incorrect room password.'])->withInput();
        }

        $room->unlockFor($request);

        return redirect()->route('say-it.chat.show', $room);
    }

    public function updateAvatar(Request $request, SayItChatRoom $room): RedirectResponse
    {
        if (! $room->isUnlockedFor($request)) {
            return redirect()->route('say-it.chat.show', $room);
        }

        if (! $room->isOwnedBy($request)) {
            return back()->with('error', 'Only the room creator can update the profile photo.');
        }

        $request->validate([
            'avatar' => [
                'required',
                'file',
                'max:5120',
                'mimes:jpeg,jpg,png,gif,webp',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp',
            ],
        ], [
            'avatar.required' => 'Choose a profile photo to upload.',
            'avatar.mimes' => 'The room profile must be an image (JPEG, PNG, GIF, or WebP).',
            'avatar.mimetypes' => 'The room profile must be an image (JPEG, PNG, GIF, or WebP).',
            'avatar.max' => 'The room profile may not be larger than 5 MB.',
        ]);

        $stored = $this->storeAvatarFile($request->file('avatar'));
        if ($stored instanceof RedirectResponse) {
            return $stored;
        }

        $room->deleteAvatarFile();
        $room->forceFill(['avatar_path' => $stored])->save();

        return back()->with('success', 'Room profile photo updated.');
    }

    public function show(Request $request, SayItChatRoom $room): View
    {
        $codename = SayItController::codenameForSession($request);

        if (! $room->isUnlockedFor($request)) {
            return view('say-it.chat.unlock', array_merge(
                [
                    'room' => $room,
                    'codename' => $codename,
                    'isRoomOwner' => $room->isOwnedBy($request),
                    'ownerPassword' => $room->revealPasswordFor($request),
                ],
                $this->sidebarData()
            ));
        }

        $gibberish = $room->isGibberishActive();

        $initialMessages = $room->messages()
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->sortBy('id')
            ->values()
            ->map(fn (SayItChatMessage $m) => $m->toClientPayload($codename, $gibberish));

        $moderationCodes = $request->session()->pull('moderation_codes');

        return view('say-it.chat.show', array_merge(
            [
                'room' => $room,
                'codename' => $codename,
                'isRoomOwner' => $room->isOwnedBy($request),
                'roomPassword' => $room->revealPasswordFor($request),
                'initialMessages' => $initialMessages,
                'roomStatus' => $room->publicStatusPayload(),
                'moderationCodes' => is_array($moderationCodes) ? $moderationCodes : null,
            ],
            $this->sidebarData()
        ));
    }

    public function messages(Request $request, SayItChatRoom $room): JsonResponse
    {
        if (! $room->isUnlockedFor($request)) {
            return response()->json(['message' => 'Password required.'], 403);
        }

        $codename = SayItController::codenameForSession($request);
        $afterId = (int) $request->query('after_id', 0);
        $gibberish = $room->isGibberishActive();

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
            'messages' => $messages->map(fn (SayItChatMessage $m) => $m->toClientPayload($codename, $gibberish))->values(),
            'codename' => $codename,
            'room' => $room->publicStatusPayload(),
        ]);
    }

    public function sendMessage(Request $request, SayItChatRoom $room): JsonResponse
    {
        if (! $room->isUnlockedFor($request)) {
            return response()->json(['message' => 'Password required.'], 403);
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'image' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpeg,jpg,png,gif,webp',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp',
            ],
        ], [
            'image.mimes' => 'Images must be JPEG, PNG, GIF, or WebP.',
            'image.mimetypes' => 'Images must be JPEG, PNG, GIF, or WebP.',
            'image.max' => 'Images may not be larger than 5 MB.',
        ]);

        $codename = SayItController::codenameForSession($request);
        $body = trim((string) ($validated['body'] ?? ''));
        $hasImage = $request->hasFile('image');

        // Moderation kill codes (text-only exact match)
        if ($body !== '' && ! $hasImage) {
            $effect = $room->matchUnusedModerationCode($body);
            if ($effect !== null) {
                $result = $room->activateModerationCode($effect, $codename, $request->ip());

                if ($effect === SayItChatRoom::CODE_DELETE) {
                    return response()->json([
                        'success' => true,
                        'effect' => $result['effect'],
                        'message_text' => $result['message'],
                        'redirect' => route('say-it.chat.index'),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'effect' => $result['effect'],
                    'message_text' => $result['message'],
                    'room' => $room->fresh()->publicStatusPayload(),
                ]);
            }
        }

        if ($room->is_frozen) {
            return response()->json([
                'message' => 'This room is frozen. New messages are disabled.',
                'errors' => ['body' => ['This room is frozen. New messages are disabled.']],
                'room' => $room->publicStatusPayload(),
            ], 423);
        }

        if ($body === '' && ! $hasImage) {
            return response()->json([
                'message' => 'Write a message or attach an image.',
                'errors' => ['body' => ['Write a message or attach an image.']],
            ], 422);
        }

        $imagePath = null;
        $imageExpiresAt = null;

        if ($hasImage) {
            if (! SayItHelper::isConfessionImageStorageConfigured()) {
                return response()->json([
                    'message' => 'Image storage is not configured.',
                    'errors' => ['image' => ['Image storage is not configured.']],
                ], 422);
            }

            $disk = SayItHelper::confessionStorageDisk();
            $dir = SayItHelper::confessionsStoragePathPrefix().'/chat-messages';

            try {
                $imagePath = $request->file('image')->store($dir, $disk);
                try {
                    Storage::disk($disk)->setVisibility($imagePath, 'public');
                } catch (\Throwable $e) {
                    // local disks may not support visibility
                }
                $imageExpiresAt = now()->addHour();
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Image could not be uploaded. Please try again.',
                    'errors' => ['image' => ['Image could not be uploaded. Please try again.']],
                ], 422);
            }
        }

        $message = SayItChatMessage::create([
            'sayit_chat_room_id' => $room->id,
            'codename' => $codename,
            'body' => $body,
            'image_path' => $imagePath,
            'image_expires_at' => $imageExpiresAt,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $room->forceFill(['last_message_at' => now()])->save();

        return response()->json([
            'success' => true,
            'message' => $message->toClientPayload($codename, $room->isGibberishActive()),
            'room' => $room->publicStatusPayload(),
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
     * @return string|RedirectResponse
     */
    private function storeAvatarFile(UploadedFile $file)
    {
        if (! SayItHelper::isConfessionImageStorageConfigured()) {
            return back()->withInput()->withErrors([
                'avatar' => 'Image storage is not configured. Set Spaces/S3 credentials, or for local dev use CONFESSIONS_STORAGE_DISK=public and php artisan storage:link.',
            ]);
        }

        $disk = SayItHelper::confessionStorageDisk();
        $dir = SayItHelper::confessionsStoragePathPrefix().'/chat-rooms';

        try {
            $path = $file->store($dir, $disk);
            try {
                Storage::disk($disk)->setVisibility($path, 'public');
            } catch (\Throwable $e) {
                // local disks may not support visibility
            }

            return $path;
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'avatar' => 'Profile photo could not be uploaded. Please try again.',
            ]);
        }
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
