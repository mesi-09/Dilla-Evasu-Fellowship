<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoworkerApplication;
use App\Models\CoworkerApplication;
use Illuminate\Http\Request;

class CoworkerApplicationController extends Controller
{
    /**
     * Only Main Admin reviews applications.
     */
    public function index()
    {
        $this->authorize('viewAny', CoworkerApplication::class);

        $applications = CoworkerApplication::latest()->paginate(15);

        return view('coworker-applications.index', compact('applications'));
    }

    /**
     * Public application form — no auth required.
     */
    public function create()
    {
        return view('coworker-applications.create');
    }

    /**
     * Anyone (guest or logged-in student) can submit an application.
     */
    public function store(StoreCoworkerApplication $request)
    {
        CoworkerApplication::create([
            ...$request->validated(),
            'user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('coworker-applications.thank-you')
            ->with('status', 'Thank you — your application has been received.');
    }

    /**
     * Public confirmation page after submitting.
     */
    public function thankYou()
    {
        return view('coworker-applications.thank-you');
    }

    public function show(CoworkerApplication $coworkerApplication)
    {
        $this->authorize('view', $coworkerApplication);

        return view('coworker-applications.show', compact('coworkerApplication'));
    }

    public function update(Request $request, CoworkerApplication $coworkerApplication)
    {
        $this->authorize('update', $coworkerApplication);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,accepted,rejected'],
        ]);

        $coworkerApplication->update([
            ...$validated,
            'reviewed_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Application updated.');
    }
}