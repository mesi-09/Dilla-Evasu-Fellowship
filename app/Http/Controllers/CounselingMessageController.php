<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCounselingMessage;
use App\Models\CounselingMessage;
use App\Models\CounselingRequest;

class CounselingMessageController extends Controller
{
    /**
     * Show the private message thread for a given Counseling request.
     */
    public function index(CounselingRequest $counselingRequest)
    {
        $this->authorize('create', [CounselingMessage::class, $counselingRequest]);

        $messages = $counselingRequest->messages()->with('sender')->oldest()->get();

        return view('counseling.messages', compact('counselingRequest', 'messages'));
    }

    /**
     * Post a new message into the thread.
     */
    public function store(StoreCounselingMessage $request, CounselingRequest $counselingRequest)
    {
        $counselingRequest->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);

        return redirect()
            ->route('counseling.messages.index', $counselingRequest)
            ->with('status', 'Message sent.');
    }
}