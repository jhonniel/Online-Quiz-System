<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::with(['readBy', 'repliedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::new()->count(),
            'read' => ContactMessage::read()->count(),
            'replied' => ContactMessage::replied()->count(),
            'closed' => ContactMessage::closed()->count(),
        ];

        return view('admin.contact-messages.index', compact('messages', 'stats'));
    }

    public function show(ContactMessage $contactMessage)
    {
        // Mark as read if it's new
        if ($contactMessage->isNew()) {
            $contactMessage->markAsRead(auth()->user());
        }

        $contactMessage->load(['readBy', 'repliedBy']);

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function reply(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'admin_reply' => 'required|string|max:2000',
        ]);

        $contactMessage->reply($request->admin_reply, auth()->user());

        return redirect()->route('contact-messages.show', $contactMessage)
            ->with('success', 'Reply sent successfully.');
    }

    public function close(ContactMessage $contactMessage)
    {
        $contactMessage->close();

        return redirect()->route('contact-messages.index')
            ->with('success', 'Message closed successfully.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('contact-messages.index')
            ->with('success', 'Message deleted successfully.');
    }
}
