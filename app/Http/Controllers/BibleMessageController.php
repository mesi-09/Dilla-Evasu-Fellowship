<?php

namespace App\Http\Controllers;

use App\Models\BibleMessage;

class BibleMessageController extends Controller
{
    /**
     * Public list: published messages only. Drafts and future-dated
     * scheduled messages never appear here.
     */
    public function index()
    {
        $messages = BibleMessage::where('status', 'published')
            ->orderByDesc('publish_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('bible-messages.index', compact('messages'));
    }

    public function show(BibleMessage $bibleMessage)
    {
        // Someone guessing an ID must not be able to read an unpublished message.
        abort_unless($bibleMessage->status === 'published', 404);

        return view('bible-messages.show', compact('bibleMessage'));
    }
}