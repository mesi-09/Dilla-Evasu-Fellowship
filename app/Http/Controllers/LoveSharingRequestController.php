<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoveSharingRequest;
use App\Models\LoveSharingRequest;
use Illuminate\Http\Request;

class LoveSharingRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * - Member: sees only their own requests.
     * - Love Sharing Leader: sees all requests.
     * - Main Admin: blocked entirely by the Policy.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', LoveSharingRequest::class);

        $user = $request->user();

        $requests = $user->isLoveSharingLeader()
            ? LoveSharingRequest::latest()->paginate(15)
            : $user->loveSharingRequests()->latest()->paginate(15);

        return view('love-sharing.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', LoveSharingRequest::class);

        return view('love-sharing.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLoveSharingRequest $request)
    {
        $loveSharingRequest = $request->user()->loveSharingRequests()->create(
            $request->validated()
        );

        return redirect()
            ->route('love-sharing.show', $loveSharingRequest)
            ->with('status', 'Your request was submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LoveSharingRequest $loveSharingRequest)
    {
        $this->authorize('view', $loveSharingRequest);

        return view('love-sharing.show', compact('loveSharingRequest'));
    }

    /**
     * Update the specified resource in storage (status/assignment changes by leader).
     */
    public function update(Request $request, LoveSharingRequest $loveSharingRequest)
    {
        $this->authorize('update', $loveSharingRequest);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,accepted,in_progress,completed,rejected,cancelled'],
            'assigned_leader_id' => ['nullable', 'exists:users,id'],
        ]);

        $loveSharingRequest->update($validated);

        return back()->with('status', 'Request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoveSharingRequest $loveSharingRequest)
    {
        $this->authorize('delete', $loveSharingRequest);

        $loveSharingRequest->delete();

        return redirect()
            ->route('love-sharing.index')
            ->with('status', 'Request cancelled.');
    }
}