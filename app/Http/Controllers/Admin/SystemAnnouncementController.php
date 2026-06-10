<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAnnouncementAcknowledgment;
use App\Models\SystemAnnouncement;
use Illuminate\Http\Request;

class SystemAnnouncementController extends Controller
{
    public function index()
    {
        $announcements = SystemAnnouncement::query()
            ->with('creator:id,name')
            ->withCount('acknowledgments')
            ->latest('created_at')
            ->paginate(15);

        $stats = [
            'total' => SystemAnnouncement::query()->count(),
            'published' => SystemAnnouncement::query()->where('is_published', true)->count(),
            'drafts' => SystemAnnouncement::query()->where('is_published', false)->count(),
            'acknowledgments' => (int) EmployeeAnnouncementAcknowledgment::query()->count(),
        ];

        return view('admin.system-announcements.index', compact('announcements', 'stats'));
    }

    public function create()
    {
        return view('admin.system-announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:65000',
            'publish_now' => 'nullable|boolean',
        ]);

        $publishNow = $request->boolean('publish_now');

        SystemAnnouncement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_published' => $publishNow,
            'published_at' => $publishNow ? now() : null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.system-announcements.index')
            ->with('success', $publishNow
                ? 'Announcement published. Employees will see it on their next login.'
                : 'Announcement saved as draft.');
    }

    public function edit(SystemAnnouncement $systemAnnouncement)
    {
        $systemAnnouncement->load(['creator:id,name'])->loadCount('acknowledgments');

        return view('admin.system-announcements.edit', [
            'announcement' => $systemAnnouncement,
        ]);
    }

    public function update(Request $request, SystemAnnouncement $systemAnnouncement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:65000',
        ]);

        $systemAnnouncement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return redirect()
            ->route('admin.system-announcements.index')
            ->with('success', 'Announcement updated.');
    }

    public function destroy(SystemAnnouncement $systemAnnouncement)
    {
        $systemAnnouncement->delete();

        return redirect()
            ->route('admin.system-announcements.index')
            ->with('success', 'Announcement deleted.');
    }

    public function publish(SystemAnnouncement $systemAnnouncement)
    {
        $systemAnnouncement->update([
            'is_published' => true,
            'published_at' => $systemAnnouncement->published_at ?? now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Announcement published. Employees who have not agreed yet will see it on login.');
    }

    public function unpublish(SystemAnnouncement $systemAnnouncement)
    {
        $systemAnnouncement->update([
            'is_published' => false,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Announcement unpublished.');
    }
}
