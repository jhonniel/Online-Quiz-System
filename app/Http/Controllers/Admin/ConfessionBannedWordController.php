<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfessionBannedWord;
use Illuminate\Http\Request;

class ConfessionBannedWordController extends Controller
{
    private function ensureFullAccess(): void
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Full admin access required for Confession settings.');
        }
    }

    public function index()
    {
        $this->ensureFullAccess();
        $words = ConfessionBannedWord::orderBy('word')->get();
        return view('admin.confession.banned-words', compact('words'));
    }

    public function store(Request $request)
    {
        $this->ensureFullAccess();
        $validated = $request->validate([
            'word' => 'required|string|max:100',
            'display_style' => 'required|in:full,first_last,end_only',
        ]);
        $word = strtolower(trim($validated['word']));
        if ($word === '') {
            return back()->withErrors(['word' => 'Please enter a word.']);
        }
        if (ConfessionBannedWord::where('word', $word)->exists()) {
            return back()->withErrors(['word' => 'This word is already in the banned list.']);
        }
        ConfessionBannedWord::create([
            'word' => $word,
            'display_style' => $validated['display_style'],
        ]);
        return redirect()->to(url('admin/confession/banned-words'))->with('success', 'Banned word added.');
    }

    public function destroy(ConfessionBannedWord $banned_word)
    {
        $this->ensureFullAccess();
        $banned_word->delete();
        return redirect()->to(url('admin/confession/banned-words'))->with('success', 'Banned word removed.');
    }
}
