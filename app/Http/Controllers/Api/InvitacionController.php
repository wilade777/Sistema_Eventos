<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitacion;
use App\Models\Evento;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;

class InvitacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Autorizar la acción viewAny
        $this->authorize('viewAny', Invitacion::class);

        $invitaciones = Invitacion::with('evento', 'asistente')->get();
        return response()->json($invitaciones);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Autorizar la acción create
        $this->authorize('create', Invitacion::class);

        $request->validate([
            'idEvento' => 'required|exists:eventos,idEvento',
            'idAsistente' => 'required|exists:usuarios,idUsuario',
            'estadoRSVP' => ['required', 'string', Rule::in(['pendiente', 'aceptado', 'rechazado'])],
        ]);

        $invitacion = Invitacion::create($request->all());

        return response()->json($invitacion, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invitacion = Invitacion::with('evento', 'asistente')->find($id);

        if (!$invitacion) {
            return response()->json(['message' => 'Invitación no encontrada'], 404);
        }

        // Autorizar la acción view
        $this->authorize('view', $invitacion);

        return response()->json($invitacion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invitacion = Invitacion::find($id);

        if (!$invitacion) {
            return response()->json(['message' => 'Invitación no encontrada'], 404);
        }

        // Autorizar la acción update
        $this->authorize('update', $invitacion);

        $request->validate([
            'idEvento' => 'sometimes|exists:eventos,idEvento',
            'idAsistente' => 'sometimes|exists:usuarios,idUsuario',
            'estadoRSVP' => ['sometimes', 'string', Rule::in(['pendiente', 'aceptado', 'rechazado'])],
        ]);

        $invitacion->update($request->all());

        return response()->json($invitacion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $invitacion = Invitacion::find($id);

        if (!$invitacion) {
            return response()->json(['message' => 'Invitación no encontrada'], 404);
        }

        // Autorizar la acción delete
        $this->authorize('delete', $invitacion);

        $invitacion->delete();

        return response()->json(['message' => 'Invitación eliminada correctamente'], 204);
    }

    public function actualizarRSVP(Request $request, string $id)
    {
        $invitacion = Invitacion::find($id);

        if (!$invitacion) {
            return response()->json(['message' => 'Invitación no encontrada'], 404);
        }

        // Autorizar que solo el asistente invitado puede actualizar su RSVP
        if (auth()->user()->idUsuario !== $invitacion->idAsistente) {
            return response()->json(['message' => 'No autorizado para actualizar este RSVP.'], 403);
        }

        $request->validate([
            'estadoRSVP' => ['required', 'string', Rule::in(['aceptado', 'rechazado'])],
        ]);

        $invitacion->estadoRSVP = $request->estadoRSVP;
        $invitacion->save();

        return response()->json(['message' => 'RSVP actualizado correctamente', 'invitacion' => $invitacion]);
    }

    // Método para enviar invitación a un asistente para un evento
    public function sendInvitation(Request $request)
    {
        $request->validate([
            'idEvento' => 'required|exists:eventos,idEvento',
            'idAsistente' => 'required|exists:usuarios,idUsuario',
        ]);

        $evento = Evento::with('organizador.secretariasAsignadas')->find($request->idEvento);
        $asistente = Usuario::find($request->idAsistente);
        $user = auth()->user();

        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado.'], 404);
        }

        if (!$asistente || $asistente->rol !== 'Asistente') {
            return response()->json(['message' => 'El ID de asistente proporcionado no es válido o no corresponde a un asistente.'], 400);
        }

        // Lógica de autorización
        if ($user->rol === 'Organizador') {
            if ($user->idUsuario !== $evento->idOrganizador) {
                return response()->json(['message' => 'No autorizado para enviar invitaciones para este evento.'], 403);
            }
        } elseif ($user->rol === 'Secretaria') {
            $organizador = $evento->organizador;
            if (is_null($organizador) || !($organizador->secretariasAsignadas instanceof Collection) || !$organizador->secretariasAsignadas->contains($user->idUsuario)) {
                return response()->json(['message' => 'No autorizado: No eres secretaria de este organizador o el evento no tiene un organizador válido.'], 403);
            }
        } elseif ($user->rol !== 'Administrador') {
            return response()->json(['message' => 'No autorizado para enviar invitaciones.'], 403);
        }

        // Verificar si la invitación ya existe para evitar duplicados
        $existingInvitation = Invitacion::where('idEvento', $request->idEvento)
                                        ->where('idAsistente', $request->idAsistente)
                                        ->first();

        if ($existingInvitation) {
            return response()->json(['message' => 'Ya existe una invitación para este asistente en este evento.'], 409); // 409 Conflict
        }

        // Crear la invitación
        $invitacion = Invitacion::create([
            'idEvento' => $request->idEvento,
            'idAsistente' => $request->idAsistente,
            'estadoRSVP' => 'pendiente', // Estado por defecto
        ]);

        return response()->json(['message' => 'Invitación enviada exitosamente.', 'invitacion' => $invitacion], 201);
    }

    // NUEVO MÉTODO: Obtener invitaciones del usuario autenticado
    public function getMyInvitations(Request $request)
    {
        $user = $request->user(); // Obtener el usuario autenticado

        if (!$user || !$user->esAsistente()) {
            return response()->json(['message' => 'No autorizado para ver invitaciones o el usuario no es un asistente.'], 403);
        }

        // Obtener todas las invitaciones donde el idAsistente coincide con el id del usuario autenticado
        // Cargar la relación 'evento' para mostrar detalles del evento
        $invitaciones = Invitacion::with('evento')->where('idAsistente', $user->idUsuario)->get();

        return response()->json($invitaciones);
    }
}