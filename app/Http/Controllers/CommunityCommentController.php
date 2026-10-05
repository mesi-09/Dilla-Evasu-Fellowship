<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunityComment;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Services\CommunityModerationService;

class CommunityCommentController extends Controller
{
    public function __construct(
        protected CommunityModerationService $moderation
    ) {
    }

    /**
     * Post a comment on a community post. Checked against the
     * prohibited-word list before saving, same as top-level posts.
     */
    public function store(StoreCommunityComment $request, CommunityPost $communityPost)
    {
        $validated = $request->validated();

        if (! $this->moderation->isSafe($validated['body'])) {
            return back()
                ->withInput()
                ->withErrors(['body' => 'Your message could not be posted because it contains inappropriate language. Please use respectful language.']);
        }

        $communityPost->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'moderation_status' => 'safe',
        ]);

        return back()->with('status', 'Comment posted.');
    }

    public function destroy(CommunityComment $communityComment)
    {
        $this->authorize('delete', $communityComment);

        $communityComment->delete();

        return back()->with('status', 'Comment removed.');
    }
}