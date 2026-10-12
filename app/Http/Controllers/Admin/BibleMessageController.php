<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BibleMessageRequest;
use App\Models\BibleMessage;
use App\Services\AuditLogService;

class BibleMessageController extends Controller
{
    public function __construct(
        protected AuditLogService $audit
    ) {
    }

    public function index()
    {
        $messages = BibleMessage::orderByDesc('publish_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.bible-messages.index', compact('messages'));
    }

    public function create()
    {
        return view('admin.bible-messages.create');
    }

    public function store(BibleMessageRequest $request)
    {
        $data = $request->validated();

        // Publishing straight away stamps the time and defaults the date to today.
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
            $data['publish_date'] = $data['publish_date'] ?? today()->toDateString();
        }

        $message = BibleMessage::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        $this->audit->bibleMessageCreated($request->user(), $message);

        return redirect()
            ->route('admin.bible-messages.index')
            ->with('status', 'Bible message saved.');
    }

    public function edit(BibleMessage $bibleMessage)
    {
        return view('admin.bible-messages.edit', compact('bibleMessage'));
    }

    public function update(BibleMessageRequest $request, BibleMessage $bibleMessage)
    {
        $data = $request->validated();

        if ($data['status'] === 'published') {
            $data['published_at'] = $bibleMessage->published_at ?? now();
            $data['publish_date'] = $data['publish_date'] ?? today()->toDateString();
        } else {
            // Moving a message back to draft/scheduled takes it off the public page.
            $data['published_at'] = null;
        }

        $bibleMessage->update($data);

        $this->audit->bibleMessageUpdated($request->user(), $bibleMessage);

        return redirect()
            ->route('admin.bible-messages.index')
            ->with('status', 'Bible message updated.');
    }

       public function destroy(\Illuminate\Http\Request $request, BibleMessage $bibleMessage)
    {
        $bibleMessage->delete();

        $this->audit->bibleMessageDeleted($request->user(), $bibleMessage);

        return redirect()
            ->route('admin.bible-messages.index')
            ->with('status', 'Bible message removed.');
    }
}