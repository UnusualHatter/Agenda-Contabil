<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Appointments\Actions\ChangeAppointmentStatus;
use App\Domain\Appointments\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Support\DisplayTimezone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Opening the link changes nothing: mail scanners open links on their own.
class ReminderResponseController extends Controller
{
    public function show(Appointment $appointment): View
    {
        $appointment->load(['client', 'service', 'documents']);

        return view('reminders.respond', [
            'appointment' => $appointment,
            'start' => DisplayTimezone::toLocal($appointment->starts_at),
            'end' => DisplayTimezone::toLocal($appointment->ends_at),
        ]);
    }

    public function store(Request $request, Appointment $appointment, ChangeAppointmentStatus $change): RedirectResponse
    {
        $answer = $request->validate([
            'answer' => ['required', Rule::in(['confirm', 'cancel'])],
        ])['answer'];

        $next = $answer === 'confirm' ? AppointmentStatus::Confirmed : AppointmentStatus::Cancelled;

        if (! $appointment->status->canTransitionTo($next)) {
            return redirect()->to($request->fullUrl())->with('notice', __('reminders.page.unchanged'));
        }

        $change->handle(null, $appointment, $next);

        return redirect()->to($request->fullUrl())->with('notice', __("reminders.page.done_{$answer}"));
    }
}
