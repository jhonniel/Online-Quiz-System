<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketProblemType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketProblemTypeController extends Controller
{
    public function index()
    {
        $types = TicketProblemType::ordered()->get();
        return view('admin.tickets.problem-types.index', compact('types'));
    }

    public function edit(TicketProblemType $ticket_problem_type)
    {
        return view('admin.tickets.problem-types.edit', ['type' => $ticket_problem_type]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);
        $slug = Str::slug(Str::lower($validated['label']));
        if (! $slug) {
            $slug = 'type-' . uniqid();
        }
        if (TicketProblemType::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . substr(uniqid(), -4);
        }
        $maxOrder = TicketProblemType::max('sort_order') ?? 0;
        TicketProblemType::create([
            'slug' => $slug,
            'label' => $validated['label'],
            'sort_order' => $maxOrder + 1,
        ]);
        return redirect()->back()->with('success', 'Problem type added.');
    }

    public function update(Request $request, TicketProblemType $ticket_problem_type)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);
        $ticket_problem_type->update(['label' => $validated['label']]);
        return redirect()->back()->with('success', 'Problem type updated.');
    }

    public function destroy(TicketProblemType $ticket_problem_type)
    {
        $ticket_problem_type->delete();
        return redirect()->back()->with('success', 'Problem type removed.');
    }
}
