<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAnnouncementAcknowledgment;
use App\Models\SystemAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $validated = $this->validatedAnnouncement($request);
        $publishNow = $request->boolean('publish_now');

        SystemAnnouncement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'feature_links' => $validated['feature_links'],
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
        $validated = $this->validatedAnnouncement($request);

        $systemAnnouncement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'feature_links' => $validated['feature_links'],
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

    /**
     * @return array<string, mixed>
     */
    private function validatedAnnouncement(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:65000',
            'feature_links' => 'nullable|array|max:10',
            'feature_links.*.url' => 'nullable|string|max:500',
            'feature_links.*.label' => 'nullable|string|max:100',
            'publish_now' => 'nullable|boolean',
        ]);

        $featureLinks = $this->normalizeFeatureLinks($request);
        $errors = [];

        foreach ($featureLinks as $index => $link) {
            if ($link['url'] === '') {
                $errors["feature_links.{$index}.url"] = 'URL or path is required.';
                continue;
            }

            if ($link['label'] === '') {
                $errors["feature_links.{$index}.label"] = 'Button label is required.';
                continue;
            }

            if (! str_starts_with($link['url'], '/')
                && ! (filter_var($link['url'], FILTER_VALIDATE_URL) && $this->isSameAppHost($link['url']))) {
                $errors["feature_links.{$index}.url"] = 'Must be a path starting with / or a URL on this site.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $validated['feature_links'] = $featureLinks !== [] ? $featureLinks : null;

        return $validated;
    }

    /**
     * @return list<array{url: string, label: string}>
     */
    private function normalizeFeatureLinks(Request $request): array
    {
        return collect($request->input('feature_links', []))
            ->map(fn (mixed $link): array => [
                'url' => trim((string) (is_array($link) ? ($link['url'] ?? '') : '')),
                'label' => trim((string) (is_array($link) ? ($link['label'] ?? '') : '')),
            ])
            ->filter(fn (array $link): bool => $link['url'] !== '' || $link['label'] !== '')
            ->values()
            ->all();
    }

    private function isSameAppHost(string $url): bool
    {
        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $linkHost = parse_url($url, PHP_URL_HOST);

        return is_string($appHost)
            && is_string($linkHost)
            && strcasecmp($appHost, $linkHost) === 0;
    }
}
