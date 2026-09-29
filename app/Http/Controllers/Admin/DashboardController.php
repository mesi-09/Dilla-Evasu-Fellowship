<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Main Admin dashboard — general system statistics only.
     * Never exposes Love Sharing or Counseling request content.
     */
    public function index()
    {
        $stats = [
            'total_members' => User::count(),
            'active_members' => User::where('is_active', true)->count(),
            'by_role' => [
                'main_admin' => User::where('role', 'main_admin')->count(),
                'love_sharing_leader' => User::where('role', 'love_sharing_leader')->count(),
                'counseling_leader' => User::where('role', 'counseling_leader')->count(),
                'member' => User::where('role', 'member')->count(),
            ],
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Full member directory — name, role, join date. No private
     * request content, just account-level info.
     */
    public function members(Request $request)
    {
        $query = User::query()->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->paginate(20)->withQueryString();

        return view('admin.members', compact('members'));
    }
}