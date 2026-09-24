<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function create(Request $request): View
    {
        return view('appointments.create', [
            'start' => $request->string('inicio')->toString() ?: null,
            'end' => $request->string('fim')->toString() ?: null,
            'clientId' => $request->integer('atendido') ?: null,
        ]);
    }

    public function show(Appointment $appointment): View
    {
        return view('appointments.show', ['appointment' => $appointment]);
    }
}
