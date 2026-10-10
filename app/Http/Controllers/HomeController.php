<?php

namespace App\Http\Controllers;

use App\Models\BibleMessage;

class HomeController extends Controller
{
    public function index()
    {
        // Published only, so a draft or a future-dated message never
        // reaches the front page.
        $latestMessage = BibleMessage::where('status', 'published')
            ->orderByDesc('publish_date')
            ->orderByDesc('id')
            ->first();

        return view('home', compact('latestMessage'));
    }
}