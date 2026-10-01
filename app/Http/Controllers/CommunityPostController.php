<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Services\CommunityModerationService;
use Illuminate\Http\Request;

class CommunityPostController extends Controller
{
    public function __construct(
        protected CommunityModerationService $moderation
    ) {
    }

    /**
     * Show the community feed — safe posts only.
     */
    public function index()
    {
        $posts = CommunityPost::where('moderation_status', 'safe')
            ->with(['user', 'comments' => function ($query) {
                $query->where('moderation_status', 'safe')->with('user');
            }, 'reactions'])
            ->latest()
            ->paginate(10);

        return view('community.index', compact('posts'));
    }

    /**
     * Create a new post. Content is checked against the prohibited-word
     * list before it is saved — a blocked post never reaches the feed.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        if (! $this->moderation->isSafe($validated['body'])) {
            return back()
                ->withInput()
                ->withErrors(['body' => 'Your message could not be posted because it contains inappropriate language. Please use respectful language.']);
        }

        $request->user()->communityPosts()->create([
            'body' => $validated['body'],
            'moderation_status' => 'safe',
        ]);

        return back()->with('status', 'Posted.');
    }

    public function destroy(CommunityPost $communityPost)
    {
        $this->authorize('delete', $communityPost);

        $communityPost->delete();

        return back()->with('status', 'Post removed.');
    }
}