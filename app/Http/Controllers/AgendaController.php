<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Appointments\Actions\RescheduleAppointment;
use App\Domain\Appointments\Queries\AgendaAppointments;
use App\Http\Requests\Agenda\AgendaEventsRequest;
use App\Http\Requests\Agenda\RescheduleAppointmentRequest;
use App\Http\Resources\AgendaEventResource;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(): View
    {
        return view('agenda.index', [
            'responsibles' => User::query()->assignable()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function events(AgendaEventsRequest $request): JsonResponse
    {
        $appointments = AgendaAppointments::between(
            $request->from(),
            $request->to(),
            $request->responsibleId(),
            $request->boolean('cancelled'),
        );

        return response()->json(AgendaEventResource::collection($appointments)->resolve($request));
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment, RescheduleAppointment $reschedule): Response
    {
        $reschedule->handle($request->user(), $appointment, $request->startsAt(), $request->endsAt());

        return response()->noContent();
    }
}
