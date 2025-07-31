<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Usuario; // Asegúrate de importar el modelo Usuario
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Para usar DB::table

class EventoController extends Controller
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
        $this->authorize('viewAny', Evento::class);

        // Eager load the organizer and get the minimum ticket price for each event
        $eventos = Evento::with('organizador')
                         ->withMin('tickets', 'precio') // NUEVO: Obtener el precio mínimo de los tickets
                         ->get();

        return response()->json($eventos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Evento::class);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i:s',
            'ubicacion' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:pendiente,activo,cancelado,finalizado',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'nullable|url',
        ]);

        $evento = Evento::create([
            'nombre' => $request->nombre,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'ubicacion' => $request->ubicacion,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado,
            'imagenes' => json_encode($request->imagenes), // Guardar como JSON
            'idOrganizador' => auth()->user()->idUsuario, // Asignar el organizador autenticado
        ]);

        return response()->json($evento, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $evento = Evento::with('organizador')->find($id);

        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }

        $this->authorize('view', $evento);

        return response()->json($evento);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }

        $this->authorize('update', $evento);

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'fecha' => 'sometimes|date',
            'hora' => 'sometimes|date_format:H:i:s',
            'ubicacion' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'sometimes|in:pendiente,activo,cancelado,finalizado',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'nullable|url',
        ]);

        // Asegurarse de que las imágenes se guarden como JSON si están presentes
        if ($request->has('imagenes')) {
            $evento->imagenes = json_encode($request->imagenes);
        }

        $evento->fill($request->except('imagenes'))->save(); // Guardar otros campos

        return response()->json($evento);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }

        $this->authorize('delete', $evento);

        $evento->delete();

        return response()->json(['message' => 'Evento eliminado correctamente'], 204);
    }

    /**
     * Publish the specified event.
     */
    public function publicar(Evento $evento)
    {
        $this->authorize('publicar', $evento);

        if ($evento->estado === 'activo') {
            return response()->json(['message' => 'El evento ya está publicado.'], 409);
        }

        $evento->estado = 'activo';
        $evento->save();

        return response()->json(['message' => 'Evento publicado exitosamente.', 'evento' => $evento]);
    }

    /**
     * Hide the specified event.
     */
    public function ocultar(Evento $evento)
    {
        $this->authorize('ocultar', $evento);

        if ($evento->estado === 'pendiente') {
            return response()->json(['message' => 'El evento ya está oculto (pendiente).'], 409);
        }

        $evento->estado = 'pendiente';
        $evento->save();

        return response()->json(['message' => 'Evento ocultado exitosamente.', 'evento' => $evento]);
    }

    /**
     * Cancel the specified event.
     */
    public function cancelar(Evento $evento)
    {
        $this->authorize('cancelar', $evento);

        if ($evento->estado === 'cancelado') {
            return response()->json(['message' => 'El evento ya está cancelado.'], 409);
        }

        $evento->estado = 'cancelado';
        $evento->save();

        return response()->json(['message' => 'Evento cancelado exitosamente.', 'evento' => $evento]);
    }

    /**
     * Get attendees for a specific event.
     */
    public function obtenerAsistentes(string $idEvento)
    {
        $evento = Evento::with(['asistentes' => function($query) {
            $query->select('usuarios.idUsuario', 'usuarios.nombre', 'usuarios.correo')
                  ->withPivot('confirmacionAsistencia'); // Asegúrate de cargar el pivot
        }])->find($idEvento);

        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }

        // Autorización para ver asistentes: Organizador del evento, Secretaria asignada, o Administrador
        $user = auth()->user();
        if ($user->esOrganizador() && $user->idUsuario === $evento->idOrganizador) {
            // Autorizado
        } elseif ($user->esSecretaria()) {
            $organizador = $evento->organizador;
            if (is_null($organizador) || !$organizador->secretariasAsignadas->contains($user->idUsuario)) {
                return response()->json(['message' => 'No autorizado: No eres secretaria de este organizador.'], 403);
            }
        } elseif (!$user->esAdministrador()) {
            return response()->json(['message' => 'No autorizado para ver asistentes de este evento.'], 403);
        }


        return response()->json($evento->asistentes);
    }

    /**
     * Confirm attendance for an attendee in an event.
     */
    public function confirmAttendance(Request $request, string $idEvento, string $idAsistente)
    {
        $evento = Evento::find($idEvento);
        $asistente = Usuario::find($idAsistente);

        if (!$evento || !$asistente) {
            return response()->json(['message' => 'Evento o asistente no encontrado.'], 404);
        }

        // Autorización para confirmar asistencia: Organizador del evento, Secretaria asignada, o Administrador
        $user = auth()->user();
        if ($user->esOrganizador()) {
            if ($user->idUsuario !== $evento->idOrganizador) {
                return response()->json(['message' => 'No autorizado para confirmar asistencia en este evento.'], 403);
            }
        } elseif ($user->esSecretaria()) {
            $organizador = $evento->organizador;
            if (is_null($organizador) || !$organizador->secretariasAsignadas->contains($user->idUsuario)) {
                return response()->json(['message' => 'No autorizado: No eres secretaria de este organizador.'], 403);
            }
        } elseif (!$user->esAdministrador()) {
            return response()->json(['message' => 'No autorizado para confirmar asistencia.'], 403);
        }

        // Asegurarse de que el asistente esté realmente asociado al evento
        $pivot = DB::table('evento_asistente')
                    ->where('idEvento', $idEvento)
                    ->where('idAsistente', $idAsistente)
                    ->first();

        if (!$pivot) {
            return response()->json(['message' => 'El asistente no está registrado para este evento.'], 404);
        }

        if ($pivot->confirmacionAsistencia) {
            return response()->json(['message' => 'La asistencia de este usuario ya ha sido confirmada.'], 409);
        }

        DB::table('evento_asistente')
            ->where('idEvento', $idEvento)
            ->where('idAsistente', $idAsistente)
            ->update(['confirmacionAsistencia' => true]);

        return response()->json(['message' => 'Asistencia confirmada exitosamente.']);
    }

    /**
     * Add an existing attendee to an event.
     */
    public function addAttendeeToEvent(Request $request, string $idEvento)
    {
        $request->validate([
            'idAsistente' => 'required|exists:usuarios,idUsuario',
        ]);

        $evento = Evento::find($idEvento);
        $asistente = Usuario::find($request->idAsistente);

        if (!$evento || !$asistente) {
            return response()->json(['message' => 'Evento o asistente no encontrado.'], 404);
        }

        // Autorización: Solo el organizador del evento, una secretaria asignada, o un administrador
        $user = auth()->user();
        if ($user->esOrganizador()) {
            if ($user->idUsuario !== $evento->idOrganizador) {
                return response()->json(['message' => 'No autorizado para añadir asistentes a este evento.'], 403);
            }
        } elseif ($user->esSecretaria()) {
            $organizador = $evento->organizador;
            if (is_null($organizador) || !$organizador->secretariasAsignadas->contains($user->idUsuario)) {
                return response()->json(['message' => 'No autorizado: No eres secretaria de este organizador.'], 403);
            }
        } elseif (!$user->esAdministrador()) {
            return response()->json(['message' => 'No autorizado para añadir asistentes.'], 403);
        }

        // Verificar si el asistente ya está asociado al evento
        if ($evento->asistentes->contains($asistente->idUsuario)) {
            return response()->json(['message' => 'El asistente ya está asociado a este evento.'], 409);
        }

        // Adjuntar el asistente al evento con confirmacionAsistencia en false por defecto
        $evento->asistentes()->attach($asistente->idUsuario, ['confirmacionAsistencia' => false]);

        return response()->json(['message' => 'Asistente añadido al evento exitosamente.']);
    }
}