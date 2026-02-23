<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfessionBannedWord;
use App\Models\ConfessionTopic;
use Illuminate\Http\Request;

class ConfessionBannedWordController extends Controller
{
    /**
     * Access controlled by admin.permission:confession middleware.
     */
    public function index()
    {
        $words = ConfessionBannedWord::orderBy('word')->get();
        $topics = ConfessionTopic::orderByDesc('posts_count')->orderBy('name')->get();
        return view('admin.confession.banned-words', compact('words', 'topics'));
    }

    public function store(Request $request)
    {
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
        $banned_word->delete();
        return redirect()->to(url('admin/confession/banned-words'))->with('success', 'Banned word removed.');
    }
}
