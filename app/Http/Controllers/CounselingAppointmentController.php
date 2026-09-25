<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCounselingAppointment;
use App\Models\CounselingAppointment;
use Illuminate\Http\Request;

class CounselingAppointmentController extends Controller
{
    /**
     * - Member: sees only their own appointments.
     * - Counseling Leader: sees all appointments.
     * - Main Admin & Love Sharing Leader: blocked entirely by the Policy.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CounselingAppointment::class);

        $user = $request->user();

        $appointments = $user->isCounselingLeader()
            ? CounselingAppointment::latest('appointment_date')->paginate(15)
            : CounselingAppointment::where('student_id', $user->id)->latest('appointment_date')->paginate(15);

        return view('counseling-appointments.index', compact('appointments'));
    }

    /**
     * Show the form for the Counseling Leader to schedule a new appointment
     * for a given counseling request.
     */
    public function create(Request $request)
    {
        $this->authorize('create', CounselingAppointment::class);

        $counselingRequestId = $request->query('counseling_request_id');

        return view('counseling-appointments.create', compact('counselingRequestId'));
    }

    public function store(StoreCounselingAppointment $request)
    {
        $this->authorize('create', CounselingAppointment::class);

        $appointment = CounselingAppointment::create([
            ...$request->validated(),
            'counselor_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('counseling-appointments.show', $appointment)
            ->with('status', 'The appointment was successfully scheduled.');
    }

    public function show(CounselingAppointment $counselingAppointment)
    {
        $this->authorize('view', $counselingAppointment);

        return view('counseling-appointments.show', compact('counselingAppointment'));
    }

    public function update(Request $request, CounselingAppointment $counselingAppointment)
    {
        $this->authorize('update', $counselingAppointment);

        $validated = $request->validate([
            'status' => ['required', 'in:requested,scheduled,confirmed,completed,cancelled,rescheduled'],
            'appointment_date' => ['nullable', 'date'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $counselingAppointment->update($validated);

        return back()->with('status', 'Appointment updated successfully.');
    }

    public function destroy(CounselingAppointment $counselingAppointment)
    {
        $this->authorize('delete', $counselingAppointment);

        $counselingAppointment->delete();

        return redirect()
            ->route('counseling-appointments.index')
            ->with('status', 'Appointment cancelled.');
    }
}