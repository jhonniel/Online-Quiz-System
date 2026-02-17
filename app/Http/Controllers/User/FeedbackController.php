<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class FeedbackController extends Controller
{
    /**
     * Display the feedback form
     */
    public function index()
    {
        $userFeedbacks = auth()->user()->feedbacks()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.feedback.index', compact('userFeedbacks'));
    }

    /**
     * Show the feedback form
     */
    public function create()
    {
        return view('user.feedback.create');
    }

    /**
     * Store a new feedback
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['bug_report', 'feature_request', 'improvement', 'general'])],
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:2000',
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
        ], [
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each image must be a file of type: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Each image may not be greater than 5MB.',
        ]);

        // Handle multiple image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            $images = $request->file('images');

            // Limit to maximum 5 images
            $images = array_slice($images, 0, 5);

            foreach ($images as $image) {
                $imageName = time() . '_' . uniqid() . '_' . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('feedback-images', $imageName, 'public');
                $imagePaths[] = $imagePath;
            }
        }

        $validated['images'] = $imagePaths;

        $feedback = auth()->user()->feedbacks()->create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Feedback submitted successfully! Thank you for helping us improve the system.',
                'type' => 'success',
                'redirect_url' => url('/feedback')
            ]);
        }

        return redirect('/feedback')
            ->with('success', 'Feedback submitted successfully! Thank you for helping us improve the system.');
    }

    /**
     * Display a specific feedback
     */
    public function show(Feedback $feedback)
    {
        // Ensure the user can only view their own feedback
        if ($feedback->user_id !== auth()->id()) {
            abort(403, 'You are not authorized to view this feedback.');
        }

        return view('user.feedback.show', compact('feedback'));
    }

    /**
     * Get feedback statistics for the user
     */
    public function getStats()
    {
        $user = auth()->user();

        $stats = [
            'total_feedbacks' => $user->feedbacks()->count(),
            'pending_feedbacks' => $user->feedbacks()->where('status', 'pending')->count(),
            'in_review_feedbacks' => $user->feedbacks()->where('status', 'in_review')->count(),
            'completed_feedbacks' => $user->feedbacks()->where('status', 'completed')->count(),
            'recent_feedbacks' => $user->feedbacks()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
        ];

        return response()->json($stats);
    }
}
