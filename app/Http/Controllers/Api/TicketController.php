<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Asegúrate de que esta línea esté presente

class TicketController extends Controller
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
        $this->authorize('viewAny', Ticket::class);
        $tickets = Ticket::with('evento', 'asistente')->get();
        return response()->json($tickets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Ticket::class);

        $request->validate([
            'idEvento' => 'required|exists:eventos,idEvento',
            'idAsistente' => 'required|exists:usuarios,idUsuario',
            'tipo' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
        ]);

        // Generar un código QR único para el ticket
        $codigoQR = Str::uuid(); // Usar UUID para asegurar unicidad

        $ticket = Ticket::create([
            'idEvento' => $request->idEvento,
            'idAsistente' => $request->idAsistente,
            'tipo' => $request->tipo,
            'precio' => $request->precio,
            'codigoQR' => $codigoQR,
            'usado' => false, // Por defecto, el ticket no ha sido usado
        ]);

        return response()->json($ticket, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ticket = Ticket::with('evento', 'asistente')->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        $this->authorize('view', $ticket);

        return response()->json($ticket);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        $this->authorize('update', $ticket);

        $request->validate([
            'idEvento' => 'sometimes|exists:eventos,idEvento',
            'idAsistente' => 'sometimes|exists:usuarios,idUsuario',
            'tipo' => 'sometimes|string|max:255',
            'precio' => 'sometimes|numeric|min:0',
            'usado' => 'sometimes|boolean',
        ]);

        $ticket->update($request->all());

        return response()->json($ticket);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        $this->authorize('delete', $ticket);

        $ticket->delete();

        return response()->json(['message' => 'Ticket eliminado correctamente'], 204);
    }

    // NUEVO MÉTODO: Obtener tickets del usuario autenticado
    public function getMyTickets(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->esAsistente()) {
            return response()->json(['message' => 'No autorizado para ver tickets o el usuario no es un asistente.'], 403);
        }

        $tickets = Ticket::with('evento')->where('idAsistente', $user->idUsuario)->get();

        return response()->json($tickets);
    }

    // NUEVO MÉTODO: Generar PDF del ticket con QR
    public function generateTicketPdf(string $codigoQR)
    {
        $ticket = Ticket::with('evento', 'asistente')->where('codigoQR', $codigoQR)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado o código QR inválido'], 404);
        }

        // Autorización: Solo el asistente dueño del ticket o un administrador/organizador/secretaria puede ver el PDF
        $user = auth()->user();
        if ($user->idUsuario !== $ticket->idAsistente && !$user->esAdministrador() && !$user->esOrganizador() && !($user->esSecretaria() && $ticket->evento->organizador->secretariasAsignadas->contains($user->idUsuario))) {
            return response()->json(['message' => 'No autorizado para ver este ticket.'], 403);
        }

        // --- CAMBIO AQUÍ: Generar el código QR como PNG y codificar en Base64 ---
        $qrCodePngBase64 = base64_encode(QrCode::format('png')->size(200)->generate($ticket->codigoQR));
        // --- FIN DEL CAMBIO ---

        $data = [
            'ticket' => $ticket,
            'qrCodePngBase64' => $qrCodePngBase64, // Pasar la imagen Base64 a la vista
        ];

        $pdf = Pdf::loadView('tickets.ticket_pdf', $data);

        return $pdf->download("ticket_{$ticket->codigoQR}.pdf");
    }

    // NUEVO MÉTODO: Validar código QR del ticket
    public function validateQrCode(Request $request)
    {
        $request->validate([
            'codigoQR' => 'required|string',
        ]);

        $ticket = Ticket::with('evento', 'asistente')->where('codigoQR', $request->codigoQR)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Código QR inválido o ticket no encontrado.'], 404);
        }

        // Autorización: Solo el organizador del evento o una secretaria asignada a ese organizador, o un administrador
        $user = auth()->user();
        if ($user->esOrganizador()) {
            if ($user->idUsuario !== $ticket->evento->idOrganizador) {
                return response()->json(['message' => 'No autorizado para validar tickets de este evento.'], 403);
            }
        } elseif ($user->esSecretaria()) {
            $organizador = $ticket->evento->organizador;
            if (is_null($organizador) || !$organizador->secretariasAsignadas->contains($user->idUsuario)) {
                return response()->json(['message' => 'No autorizado: No eres secretaria de este organizador.'], 403);
            }
        } elseif (!$user->esAdministrador()) {
            return response()->json(['message' => 'No autorizado para validar tickets.'], 403);
        }

        if ($ticket->usado) {
            return response()->json(['message' => 'Ticket ya ha sido usado.', 'ticket' => $ticket], 409); // 409 Conflict
        }

        // Marcar el ticket como usado
        $ticket->usado = true;
        $ticket->save();

        return response()->json(['message' => 'Ticket validado exitosamente. Asistencia registrada.', 'ticket' => $ticket]);
    }
}