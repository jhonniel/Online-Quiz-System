<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $news = News::latest('created_at')->paginate(15);
        return view('admin.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_url' => 'nullable|url|max:500',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_published'] = $request->has('is_published') ? true : false;
        
        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('news', 'public');
            $validated['image_path'] = $imagePath;
            $validated['image_url'] = Storage::disk('public')->url($imagePath);
        }

        // Remove image from validated array as it's not a database field
        unset($validated['image']);

        News::create($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'News created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        // Increment views
        $news->increment('views');
        
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_url' => 'nullable|url|max:500',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['is_published'] = $request->has('is_published') ? true : false;
        
        if ($validated['is_published'] && empty($validated['published_at']) && !$news->published_at) {
            $validated['published_at'] = now();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($news->image_path && Storage::disk('public')->exists($news->image_path)) {
                Storage::disk('public')->delete($news->image_path);
            }
            
            $imagePath = $request->file('image')->store('news', 'public');
            $validated['image_path'] = $imagePath;
            $validated['image_url'] = Storage::disk('public')->url($imagePath);
        }

        // Remove image from validated array as it's not a database field
        unset($validated['image']);

        $news->update($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'News updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        // Delete associated image if exists
        if ($news->image_path && Storage::disk('public')->exists($news->image_path)) {
            Storage::disk('public')->delete($news->image_path);
        }

        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'News deleted successfully.');
    }

    /**
     * Toggle publish status
     */
    public function togglePublish(News $news)
    {
        $news->is_published = !$news->is_published;
        
        if ($news->is_published && !$news->published_at) {
            $news->published_at = now();
        }
        
        $news->save();

        return redirect()->back()
            ->with('success', $news->is_published ? 'News published successfully.' : 'News unpublished successfully.');
    }

    /**
     * Toggle news section visibility on landing page
     */
    public function toggleNewsSection(Request $request)
    {
        $enabled = $request->input('enabled', false);
        Setting::set('news_section_enabled', $enabled ? '1' : '0', 'boolean', 'Enable/Disable news section on landing page');
        
        return redirect()->back()
            ->with('success', $enabled ? 'News section enabled on landing page.' : 'News section disabled on landing page.');
    }
}
