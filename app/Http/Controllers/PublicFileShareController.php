<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Support\FileStreamResponse;
use Illuminate\Http\Request;

class PublicFileShareController extends Controller
{
    public function show(string $uuid)
    {
        $item = $this->resolvePublicItem($uuid);

        if ($item->isFile()) {
            return view('public.files.show', [
                'root' => $item,
                'item' => $item,
                'files' => collect(),
                'breadcrumbs' => collect(),
                'isFilePreview' => true,
            ]);
        }

        $files = File::query()
            ->where('folder_id', $item->id)
            ->with('uploader')
            ->orderBy('type', 'desc')
            ->orderBy('name')
            ->get();

        return view('public.files.show', [
            'root' => $item,
            'item' => $item,
            'files' => $files,
            'breadcrumbs' => $this->buildBreadcrumbs($item),
            'isFilePreview' => false,
        ]);
    }

    public function view(string $uuid)
    {
        $file = $this->resolvePublicItem($uuid);

        if ($file->isFolder()) {
            return redirect()->route('public.files.show', ['uuid' => $file->uuid]);
        }

        return FileStreamResponse::inline($file);
    }

    public function download(string $uuid)
    {
        $file = $this->resolvePublicItem($uuid);

        if ($file->isFolder()) {
            abort(404, 'Cannot download a folder.');
        }

        return FileStreamResponse::download($file);
    }

    private function resolvePublicItem(string $uuid): File
    {
        $item = File::where('uuid', $uuid)->firstOrFail();

        if (! $item->isPubliclyAccessible()) {
            abort(404, 'This shared link is unavailable.');
        }

        return $item;
    }

    /**
     * @return \Illuminate\Support\Collection<int, File>
     */
    private function buildBreadcrumbs(File $folder): \Illuminate\Support\Collection
    {
        $breadcrumbs = collect();
        $current = $folder->folder;

        while ($current) {
            if ($current->isPubliclyAccessible()) {
                $breadcrumbs->prepend($current);
            }
            $current = $current->folder;
        }

        return $breadcrumbs;
    }
}
