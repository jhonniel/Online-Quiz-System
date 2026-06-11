<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnonymousChatRoom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnonymousChatController extends Controller
{
    public function index(Request $request): View
    {
        $rooms = AnonymousChatRoom::query()
            ->with([
                'userOne:id,name,email',
                'userTwo:id,name,email',
                'creator:id,name',
                'participants',
            ])
            ->withCount('messages')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('userOne', fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'))
                        ->orWhereHas('userTwo', fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.anonymous-chat.index', compact('rooms'));
    }

    public function show(AnonymousChatRoom $anonymousChatRoom): View
    {
        $anonymousChatRoom->load([
            'userOne:id,name,email',
            'userTwo:id,name,email',
            'creator:id,name,email',
            'participants.user:id,name,email',
            'messages.sender:id,name,email',
        ]);

        return view('admin.anonymous-chat.show', [
            'room' => $anonymousChatRoom,
        ]);
    }
}
