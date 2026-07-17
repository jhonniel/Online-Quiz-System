<?php

namespace App\Http\Controllers;

use App\Helpers\SayItHelper;
use App\Models\ConfessionComment;
use App\Models\ConfessionCommentVote;
use App\Models\ConfessionHashtag;
use App\Models\ConfessionPost;
use App\Models\ConfessionPostVote;
use App\Models\ConfessionTopic;
use App\Models\Setting;
use App\Services\ConfessionCodenameService;
use App\Services\SayItImageGeneration\SayItImageBinaryValidator;
use App\Services\SayItImageGeneration\SayItImageGenerationException;
use App\Services\SayItImageGeneration\SayItImageGenerator;
use App\Services\SayItImageGeneration\SayItImageSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SayItController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'recent');
        $topicSlug = $request->get('topic');
        $hashtagSlug = $request->get('hashtag');

        $query = ConfessionPost::withCount('allComments')->with(['latestComment', 'topic']);

        if ($topicSlug) {
            $topic = ConfessionTopic::where('slug', $topicSlug)->first();
            if ($topic) {
                $query->where('confession_topic_id', $topic->id);
                $sort = 'popular';
            }
        }
        if ($hashtagSlug) {
            $query->whereHas('hashtags', fn ($q) => $q->where('confession_hashtags.slug', $hashtagSlug));
            $sort = 'popular';
        }

        // Single most popular post for featured slot at top (score = net votes + comment count)
        $mostPopularPost = ConfessionPost::withCount('allComments')
            ->with(['latestComment', 'topic'])
            ->orderByRaw(
                '(confession_posts.upvotes_count - confession_posts.downvotes_count) + '.
                '(SELECT COUNT(*) FROM confession_comments WHERE confession_comments.confession_post_id = confession_posts.id) DESC'
            )
            ->orderByDesc('created_at')
            ->first();

        if ($sort === 'popular') {
            $query->orderByRaw(
                '(confession_posts.upvotes_count - confession_posts.downvotes_count) + '.
                '(SELECT COUNT(*) FROM confession_comments WHERE confession_comments.confession_post_id = confession_posts.id) DESC'
            )->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
            if ($mostPopularPost) {
                $query->where('confession_posts.id', '!=', $mostPopularPost->id);
            }
        }

        $posts = $query->paginate(15)->withQueryString();

        $recentPosts = ConfessionPost::withCount('allComments')
            ->with(['latestComment', 'topic'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $topics = ConfessionTopic::orderByDesc('posts_count')->orderBy('name')->get(['id', 'name', 'slug', 'posts_count']);
        $topTopics = $topics->take(10);
        $topHashtags = ConfessionHashtag::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']);

        if ($request->get('lazy') || $request->ajax()) {
            $sessionCodename = self::codenameForSession($request);
            $html = view('say-it.partials.post-cards', ['posts' => $posts, 'sessionCodename' => $sessionCodename])->render();

            return response()->json([
                'html' => $html,
                'next_page_url' => $posts->hasMorePages() ? $posts->nextPageUrl() : null,
                'has_more' => $posts->hasMorePages(),
            ]);
        }

        $sessionCodename = self::codenameForSession($request);
        $sayItAiImageConfigured = SayItImageGenerator::isComposerGenerateImageAvailable();

        return view('say-it.index', compact('posts', 'recentPosts', 'sort', 'topics', 'topTopics', 'topHashtags', 'topicSlug', 'hashtagSlug', 'sessionCodename', 'mostPopularPost', 'sayItAiImageConfigured'));
    }

    public function storePost(Request $request)
    {
        $bgKeys = SayItHelper::cardBackgroundKeys();

        $validated = $request->validate([
            'topic_id' => 'nullable|exists:confession_topics,id',
            'topic_name' => 'nullable|string|max:100',
            'content' => 'nullable|string|max:10000',
            'text_size' => 'nullable|in:normal,medium,large',
            'card_background_mode' => ['nullable', Rule::in(['random', 'pick'])],
            'card_background' => [
                'exclude_unless:card_background_mode,pick',
                'required',
                Rule::in($bgKeys),
            ],
            'image' => [
                'nullable',
                'file',
                'max:5120', // 5MB
                'mimes:jpeg,jpg,png,gif,webp',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp',
            ],
            'ai_generated_image_dataurl' => ['nullable', 'string', 'max:8000000'],
        ], [
            'content.required_without' => 'Please write something or attach an image.',
            'image.image' => 'The photo must be an image (JPEG, PNG, GIF, or WebP).',
            'image.mimes' => 'The photo must be an image (JPEG, PNG, GIF, or WebP).',
            'image.mimetypes' => 'The photo must be an image (JPEG, PNG, GIF, or WebP).',
            'image.max' => 'The photo may not be larger than 5 MB.',
            'ai_generated_image_dataurl.max' => 'The AI image payload is too large.',
        ]);

        $topicId = $request->input('topic_id');
        $topicName = trim((string) $request->input('topic_name', ''));
        if (! $topicId && $topicName === '') {
            return back()->withInput()->withErrors(['topic' => 'Please select or enter a topic. BUGO KAYKA OYYY!']);
        }

        if ($topicName !== '') {
            $topic = ConfessionTopic::findOrCreateByName($topicName);
        } else {
            $topic = ConfessionTopic::findOrFail($topicId);
        }

        $aiDataUrl = (string) ($validated['ai_generated_image_dataurl'] ?? '');
        $hasAiPayload = trim($aiDataUrl) !== '';

        if ($hasAiPayload && ! SayItImageSettings::isComposerGenerateImageFeatureEnabled()) {
            return back()->withInput()->withErrors(['ai_generated_image_dataurl' => 'AI-generated images are disabled for this site.']);
        }

        if (empty(trim($validated['content'] ?? '')) && ! $request->hasFile('image') && ! $hasAiPayload) {
            return back()->withInput()->withErrors(['content' => 'Please write something, attach a photo, or attach an AI-generated image.']);
        }

        if ($request->hasFile('image') && $hasAiPayload) {
            return back()->withInput()->withErrors([
                'image' => 'Choose either an uploaded photo or an AI-generated image, not both.',
            ]);
        }

        $imagePath = null;
        $confessDisk = SayItHelper::confessionStorageDisk();
        $confessDir = SayItHelper::confessionsStoragePathPrefix();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (! SayItHelper::isConfessionImageStorageConfigured()) {
                return back()->withInput()->withErrors(['image' => 'Image storage is not configured. Set Spaces/S3 credentials, or for local dev use CONFESSIONS_STORAGE_DISK=public and php artisan storage:link.']);
            }
            try {
                $imagePath = $file->store($confessDir, $confessDisk);
                try {
                    Storage::disk($confessDisk)->setVisibility($imagePath, 'public');
                } catch (\Throwable $e) {
                    // local disks may not support visibility; Spaces/S3 should succeed
                }
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['image' => 'Photo could not be uploaded to storage. Please try again.']);
            }
        } elseif ($hasAiPayload) {
            if (! SayItHelper::isConfessionImageStorageConfigured()) {
                return back()->withInput()->withErrors(['ai_generated_image_dataurl' => 'Image storage is not configured. Set Spaces/S3 credentials, or for local dev use CONFESSIONS_STORAGE_DISK=public and php artisan storage:link.']);
            }
            try {
                $binary = $this->decodeAiGeneratedImageDataUrl($aiDataUrl);
                $ext = $this->guessImageExtensionFromBinary($binary);
                $path = $confessDir.'/ai-'.date('Y/m').'/'.Str::uuid()->toString().'.'.$ext;
                Storage::disk($confessDisk)->put($path, $binary, 'public');
                $imagePath = $path;
            } catch (ValidationException $e) {
                return back()->withInput()->withErrors($e->errors());
            } catch (\Throwable $e) {
                Log::warning('Say-it AI image upload failed: '.$e->getMessage());

                return back()->withInput()->withErrors(['ai_generated_image_dataurl' => 'The AI image could not be stored. Please try again.']);
            }
        }

        $codename = self::codenameForSession($request);

        $mode = $validated['card_background_mode'] ?? 'pick';
        $meshStyle = null;
        $cardBackground = null;
        if ($mode === 'pick') {
            $cardBackground = $validated['card_background'] ?? 'white';
        } else {
            $meshStyle = SayItHelper::randomMeshBackgroundStyle();
        }

        $post = ConfessionPost::create([
            'confession_topic_id' => $topic->id,
            'content' => $validated['content'] ?? '',
            'text_size' => $validated['text_size'] ?? 'normal',
            'card_background' => $cardBackground,
            'card_background_mesh' => $meshStyle,
            'image_path' => $imagePath,
            'codename' => $codename,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $topic->increment('posts_count');

        $content = $validated['content'] ?? '';
        $slugs = ConfessionHashtag::extractFromContent($content);
        $hashtagIds = [];
        foreach ($slugs as $slug) {
            $hashtag = ConfessionHashtag::findOrCreateBySlug($slug);
            $hashtagIds[$hashtag->id] = [];
            $hashtag->increment('posts_count');
        }
        $post->hashtags()->sync(array_keys($hashtagIds));

        return redirect(url('/Say-it'))->with('success', 'Your confession was posted. You are '.$codename.'.');
    }

    /**
     * JSON endpoint: generate an image from a prompt (SD Web UI or ComfyUI), for Say-it composer preview.
     */
    public function generateImage(Request $request)
    {
        if (! SayItImageSettings::isComposerGenerateImageFeatureEnabled()) {
            return response()->json([
                'ok' => false,
                'message' => 'AI image generation is disabled. An administrator can enable it under Admin → Settings → Say-it.',
            ], 403);
        }

        if (! SayItImageGenerator::isConfigured()) {
            return response()->json([
                'ok' => false,
                'message' => 'AI image generation is not configured. Open Admin → Settings → Say-it, choose a backend, and set Internal API URL (or Base URL) to an address this server can reach.',
            ], 503);
        }

        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        try {
            $binary = SayItImageGenerator::make()->generate(trim($validated['prompt']));
        } catch (SayItImageGenerationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Say-it generateImage: '.$e->getMessage(), ['exception' => $e]);

            $message = 'Image generation failed unexpectedly.';
            if (config('app.debug')) {
                $message .= ' '.$e->getMessage();
            }

            return response()->json(['ok' => false, 'message' => $message], 500);
        }

        $mime = 'image/png';
        if (str_starts_with($binary, "\xff\xd8\xff")) {
            $mime = 'image/jpeg';
        } elseif (strlen($binary) > 12 && str_starts_with($binary, 'RIFF') && substr($binary, 8, 4) === 'WEBP') {
            $mime = 'image/webp';
        }

        $dataUrl = 'data:'.$mime.';base64,'.base64_encode($binary);

        return response()->json([
            'ok' => true,
            'mime' => $mime,
            'data_url' => $dataUrl,
        ]);
    }

    /**
     * JSON: random mesh CSS for the Say-it composer textarea (matches posted card gradient).
     */
    public function composerMeshPreview()
    {
        return response()->json([
            'style' => SayItHelper::randomMeshBackgroundStyle(),
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * @throws ValidationException
     */
    protected function decodeAiGeneratedImageDataUrl(string $dataUrl): string
    {
        $dataUrl = trim($dataUrl);
        if (! preg_match('#^data:image/(png|jpeg|jpg|webp);base64,(.+)$#is', $dataUrl, $m)) {
            throw ValidationException::withMessages([
                'ai_generated_image_dataurl' => 'Invalid AI image format.',
            ]);
        }

        $raw = base64_decode($m[2], true);
        if ($raw === false || strlen($raw) < 32) {
            throw ValidationException::withMessages([
                'ai_generated_image_dataurl' => 'Could not decode the AI image.',
            ]);
        }

        if (strlen($raw) > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'ai_generated_image_dataurl' => 'AI image is too large (max 5 MB).',
            ]);
        }

        try {
            SayItImageBinaryValidator::assertPngJpegWebp($raw);
        } catch (SayItImageGenerationException $e) {
            throw ValidationException::withMessages([
                'ai_generated_image_dataurl' => $e->getMessage(),
            ]);
        }

        return $raw;
    }

    protected function guessImageExtensionFromBinary(string $binary): string
    {
        if (str_starts_with($binary, "\xff\xd8\xff")) {
            return 'jpg';
        }
        if (strlen($binary) > 12 && str_starts_with($binary, 'RIFF') && substr($binary, 8, 4) === 'WEBP') {
            return 'webp';
        }

        return 'png';
    }

    public function show(ConfessionPost $post)
    {
        $post->load('topic');
        $allComments = $post->allComments()->orderBy('created_at')->get();
        $topTopics = ConfessionTopic::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']);
        $topHashtags = ConfessionHashtag::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']);

        return view('say-it.show', compact('post', 'allComments', 'topTopics', 'topHashtags'));
    }

    public function storeComment(Request $request)
    {
        $validated = $request->validate([
            'confession_post_id' => 'required|exists:confession_posts,id',
            'parent_id' => 'nullable|exists:confession_comments,id',
            'content' => 'required|string|max:5000',
        ]);

        $codename = self::codenameForSession($request);

        ConfessionComment::create([
            'confession_post_id' => $validated['confession_post_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'codename' => $codename,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->to(url('/Say-it/'.$validated['confession_post_id']).'#comments')->with('success', 'Comment posted as '.$codename.'.');
    }

    public function vote(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['post', 'comment'])],
            'id' => 'required|integer',
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $voteValue = $validated['direction'] === 'up' ? 1 : -1;
        $ip = $request->ip();

        if ($validated['type'] === 'post') {
            $post = ConfessionPost::findOrFail($validated['id']);
            $existing = ConfessionPostVote::where('confession_post_id', $post->id)->where('ip_address', $ip)->first();
            if ($existing) {
                if ($existing->vote === $voteValue) {
                    $existing->delete();
                    $post->decrement($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
                } else {
                    $existing->update(['vote' => $voteValue]);
                    $post->increment($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
                    $post->decrement($voteValue === 1 ? 'downvotes_count' : 'upvotes_count');
                }
            } else {
                ConfessionPostVote::create([
                    'confession_post_id' => $post->id,
                    'ip_address' => $ip,
                    'vote' => $voteValue,
                ]);
                $post->increment($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
            }

            return response()->json(['score' => $post->fresh()->upvotes_count - $post->fresh()->downvotes_count]);
        }

        $comment = ConfessionComment::findOrFail($validated['id']);
        $existing = ConfessionCommentVote::where('confession_comment_id', $comment->id)->where('ip_address', $ip)->first();
        if ($existing) {
            if ($existing->vote === $voteValue) {
                $existing->delete();
                $comment->decrement($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
            } else {
                $existing->update(['vote' => $voteValue]);
                $comment->increment($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
                $comment->decrement($voteValue === 1 ? 'downvotes_count' : 'upvotes_count');
            }
        } else {
            ConfessionCommentVote::create([
                'confession_comment_id' => $comment->id,
                'ip_address' => $ip,
                'vote' => $voteValue,
            ]);
            $comment->increment($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
        }

        return response()->json(['score' => $comment->fresh()->upvotes_count - $comment->fresh()->downvotes_count]);
    }

    /**
     * Get the codename for the current session. Same session keeps the same codename;
     * when the session is no longer active, a new unique codename is generated.
     */
    public function destroyPost(Request $request, ConfessionPost $post)
    {
        // Check if post was created within 50 seconds
        $secondsSinceCreation = now()->diffInSeconds($post->created_at);
        if ($secondsSinceCreation > 50) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your post within 50 seconds of posting.',
            ], 403);
        }

        // Verify ownership: check codename and IP address
        $sessionCodename = $request->session()->get('sayit_codename');
        $requestIp = $request->ip();

        if ($post->codename !== $sessionCodename || $post->ip_address !== $requestIp) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own posts.',
            ], 403);
        }

        // Decrement topic count
        if ($post->confession_topic_id) {
            $post->topic()->decrement('posts_count');
        }

        // Decrement hashtag counts
        $post->load('hashtags');
        foreach ($post->hashtags as $hashtag) {
            $hashtag->decrement('posts_count');
        }

        // Delete the post (cascade will handle related votes and comments)
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.',
        ]);
    }

    public static function codenameForSession(Request $request): string
    {
        $settingsVersion = (string) Setting::get('confession_anon_name_settings_version', 'v1');
        $sessionVersion = (string) $request->session()->get('sayit_codename_settings_version', '');

        if ($request->session()->has('sayit_codename') && $sessionVersion === $settingsVersion) {
            return $request->session()->get('sayit_codename');
        }

        $codename = ConfessionCodenameService::generate();
        $request->session()->put('sayit_codename', $codename);
        $request->session()->put('sayit_codename_settings_version', $settingsVersion);

        return $codename;
    }
}
