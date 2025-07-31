<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\InvitacionController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\OrganizadorSecretariaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas de autenticación (sin protección inicial)
Route::post('usuarios/login', [UsuarioController::class, 'login']);
Route::post('usuarios', [UsuarioController::class, 'store']); // Registro de usuarios

// Rutas protegidas por Sanctum (middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('usuarios/logout', [UsuarioController::class, 'logout']);
    Route::get('usuarios/me', [UsuarioController::class, 'me']);

    // Rutas para Usuarios
    Route::apiResource('usuarios', UsuarioController::class)->except(['store']); // Excepto store, que ya está arriba

    // Rutas para Eventos
    Route::apiResource('eventos', EventoController::class);
    Route::post('eventos/{evento}/publicar', [EventoController::class, 'publicar']);
    Route::post('eventos/{evento}/ocultar', [EventoController::class, 'ocultar']);
    Route::post('eventos/{evento}/cancelar', [EventoController::class, 'cancelar']);
    Route::get('eventos/{idEvento}/asistentes', [EventoController::class, 'obtenerAsistentes']);
    Route::post('/eventos/{idEvento}/asistentes/{idAsistente}/confirm', [EventoController::class, 'confirmAttendance']);
    Route::post('eventos/{idEvento}/add-attendee', [EventoController::class, 'addAttendeeToEvent']);


    // Rutas para Invitaciones
    // IMPORTANTE: Colocar rutas específicas antes de apiResource para evitar conflictos
    Route::get('invitaciones/me', [InvitacionController::class, 'getMyInvitations']); // RUTA ESPECÍFICA ANTES DEL RECURSO
    Route::apiResource('invitaciones', InvitacionController::class); // Ruta de recurso genérica
    Route::put('invitaciones/{invitacion}/actualizar-rsvp', [InvitacionController::class, 'actualizarRSVP']);
    Route::post('invitaciones/send', [InvitacionController::class, 'sendInvitation']);


    // Rutas para Tickets
    // IMPORTANTE: Colocar rutas específicas antes de apiResource para evitar conflictos
    Route::get('tickets/me', [TicketController::class, 'getMyTickets']); // NUEVA RUTA: Mis tickets
    Route::get('tickets/pdf/{codigoQR}', [TicketController::class, 'generateTicketPdf']); // NUEVA RUTA: Generar PDF
    Route::post('tickets/validate-qr', [TicketController::class, 'validateQrCode']); // NUEVA RUTA: Validar QR
    Route::apiResource('tickets', TicketController::class); // Ruta de recurso genérica


    // Rutas para Pagos
    Route::apiResource('pagos', PagoController::class);
    Route::post('pagos/{pago}/procesar', [PagoController::class, 'procesarPago']);
    Route::get('pagos/{pago}/verificar', [PagoController::class, 'verificarPago']);

    // Rutas para la gestión de Organizador-Secretaria
    Route::post('organizadores/{idOrganizador}/secretarias/assign', [OrganizadorSecretariaController::class, 'assignSecretaria']);
    Route::post('organizadores/{idOrganizador}/secretarias/unassign', [OrganizadorSecretariaController::class, 'unassignSecretaria']);
    Route::get('organizadores/{idOrganizador}/secretarias', [OrganizadorSecretariaController::class, 'getSecretarias']);
});