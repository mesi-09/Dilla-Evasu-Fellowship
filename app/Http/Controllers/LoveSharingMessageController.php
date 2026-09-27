<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoveSharingMessage;
use App\Models\LoveSharingMessage;
use App\Models\LoveSharingRequest;

class LoveSharingMessageController extends Controller
{
    /**
     * Show the private message thread for a given Love Sharing request.
     */
    public function index(LoveSharingRequest $loveSharingRequest)
    {
        $this->authorize('create', [LoveSharingMessage::class, $loveSharingRequest]);

        $messages = $loveSharingRequest->messages()->with('sender')->oldest()->get();

        return view('love-sharing.messages', compact('loveSharingRequest', 'messages'));
    }

    /**
     * Post a new message into the thread.
     */
    public function store(StoreLoveSharingMessage $request, LoveSharingRequest $loveSharingRequest)
    {
        $loveSharingRequest->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);

        return redirect()
            ->route('love-sharing.messages.index', $loveSharingRequest)
            ->with('status', 'Message sent.');
    }
}