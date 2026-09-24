<?php

declare(strict_types=1);

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');

    Route::controller(AgendaController::class)->group(function () {
        Route::get('/agenda', 'index')->name('agenda')->can('viewAny', Appointment::class);
        Route::get('/agenda/eventos', 'events')->name('agenda.events')->can('viewAny', Appointment::class);
        Route::patch('/atendimentos/{appointment}/horario', 'reschedule')->name('appointments.reschedule');
    });

    Route::controller(AppointmentController::class)->group(function () {
        Route::get('/atendimentos/novo', 'create')->name('appointments.create')->can('create', Appointment::class);
        Route::get('/atendimentos/{appointment}', 'show')->name('appointments.show')->can('view', 'appointment');
    });

    Route::controller(ClientController::class)->group(function () {
        Route::get('/atendidos', 'index')->name('clients.index')->can('viewAny', Client::class);
        Route::get('/atendidos/novo', 'create')->name('clients.create')->can('create', Client::class);
        Route::get('/atendidos/{client}', 'show')->name('clients.show')->can('view', 'client');
        Route::get('/atendidos/{client}/editar', 'edit')->name('clients.edit')->can('update', 'client');
    });
});

require __DIR__.'/auth.php';
