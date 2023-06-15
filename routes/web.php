<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgendaDigitalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::controller(AgendaDigitalController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/cita', 'crearCita')->name('crearCita');
    Route::get('/cita/{id}', 'show')->name('mostrarCita');
    Route::post('/guardar', 'store')->name('guardarCita');
    Route::post('/guardar-citas-automaticas', 'guardarCitasAutomaticas')->name('guardarCitasAutomaticas');
    Route::get('/test', function() {
        dd(date('Y-m-d H:i:s'));
    });
});

// Route::get('/', [AgendaDigitalController::class, 'index']);
// Route::get('/cita', [AgendaDigitalController::class, 'crearCita'])->name('crearCita');
// Route::post('/guardar', [AgendaDigitalController::class, 'store'])->name('guardarCita');
