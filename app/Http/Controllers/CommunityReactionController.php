<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use Illuminate\Http\Request;

class CommunityReactionController extends Controller
{
    /**
     * Toggle the current user's reaction on a post — add it if not
     * present, remove it if already reacted.
     */
    public function toggle(Request $request, CommunityPost $communityPost)
    {
        $existing = $communityPost->reactions()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $communityPost->reactions()->create([
                'user_id' => $request->user()->id,
            ]);
        }

        return back();
    }
}