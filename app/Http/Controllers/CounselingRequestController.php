<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCounselingRequest;
use App\Models\CounselingRequest;
use Illuminate\Http\Request;

class CounselingRequestController extends Controller
{
    /**
     * - Member: sees only their own requests.
     * - Counseling Leader: sees all requests.
     * - Main Admin & Love Sharing Leader: blocked entirely by the Policy.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CounselingRequest::class);

        $user = $request->user();

        $requests = $user->isCounselingLeader()
            ? CounselingRequest::latest()->paginate(15)
            : $user->counselingRequests()->latest()->paginate(15);

        return view('counseling.index', compact('requests'));
    }

    public function create()
    {
        $this->authorize('create', CounselingRequest::class);

        return view('counseling.create');
    }

    public function store(StoreCounselingRequest $request)
    {
        $counselingRequest = $request->user()->counselingRequests()->create(
            $request->validated()
        );

        return redirect()
            ->route('counseling.show', $counselingRequest)
            ->with('status', 'Your request was submitted successfully.');
    }

    public function show(CounselingRequest $counselingRequest)
    {
        $this->authorize('view', $counselingRequest);

        return view('counseling.show', compact('counselingRequest'));
    }

    public function update(Request $request, CounselingRequest $counselingRequest)
    {
        $this->authorize('update', $counselingRequest);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,accepted,in_progress,completed,rejected,cancelled'],
            'assigned_leader_id' => ['nullable', 'exists:users,id'],
        ]);

        $counselingRequest->update($validated);

        return back()->with('status', 'Request updated successfully.');
    }

    public function destroy(CounselingRequest $counselingRequest)
    {
        $this->authorize('delete', $counselingRequest);

        $counselingRequest->delete();

        return redirect()
            ->route('counseling.index')
            ->with('status', 'Request cancelled.');
    }
}