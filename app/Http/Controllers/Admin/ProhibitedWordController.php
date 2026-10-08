<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProhibitedWord;
use Illuminate\Http\Request;

class ProhibitedWordController extends Controller
{
    public function index()
    {
        $words = ProhibitedWord::latest()->get();

        return view('admin.prohibited-words.index', compact('words'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:255', 'unique:prohibited_words,word'],
            'category' => ['required', 'in:profanity,insults,harassment,sexual,threatening,spam'],
        ]);

        ProhibitedWord::create([
            ...$validated,
            'is_active' => true,
        ]);

        return back()->with('status', 'Word added.');
    }

    public function toggle(ProhibitedWord $prohibitedWord)
    {
        $prohibitedWord->update(['is_active' => ! $prohibitedWord->is_active]);

        return back()->with('status', 'Word updated.');
    }

    public function destroy(ProhibitedWord $prohibitedWord)
    {
        $prohibitedWord->delete();

        return back()->with('status', 'Word removed.');
    }
}