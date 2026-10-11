<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark one notification as read and go to the thing it is about.
     */
    public function read(Request $request, string $id)
    {
        // Looked up through the signed-in user, so another person's
        // notification id is simply a 404.
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        // Only follow a path inside this site. Anything else (a full address,
        // "//host", or a backslash that browsers treat like a slash) is ignored.
        $isSitePath = is_string($url)
            && str_starts_with($url, '/')
            && ! str_starts_with($url, '//')
            && ! str_contains($url, '\\');

        return $isSitePath
            ? redirect($url)
            : redirect()->route('notifications.index');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}