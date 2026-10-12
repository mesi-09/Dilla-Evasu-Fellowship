<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProhibitedWord;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ProhibitedWordController extends Controller
{
    public function __construct(
        protected AuditLogService $audit
    ) {
    }

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

        $word = ProhibitedWord::create([
            ...$validated,
            'is_active' => true,
        ]);

        $this->audit->prohibitedWordAdded($request->user(), $word);

        return back()->with('status', 'Word added.');
    }

    public function toggle(Request $request, ProhibitedWord $prohibitedWord)
    {
        $prohibitedWord->update(['is_active' => ! $prohibitedWord->is_active]);

        $this->audit->prohibitedWordToggled($request->user(), $prohibitedWord);

        return back()->with('status', 'Word updated.');
    }

    public function destroy(Request $request, ProhibitedWord $prohibitedWord)
    {
        $prohibitedWord->delete();

        $this->audit->prohibitedWordRemoved($request->user(), $prohibitedWord);

        return back()->with('status', 'Word removed.');
    }
}