<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PagoController extends Controller
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
        $this->authorize('viewAny', Pago::class);

        $query = Pago::with('ticket.evento', 'ticket.asistente');

        // Filtrar para asistentes: solo ver pagos de sus propios tickets
        if (auth()->user()->esAsistente()) {
            $query->whereHas('ticket', function ($q) {
                $q->where('idAsistente', auth()->user()->idUsuario);
            });
        }

        $pagos = $query->get();
        return response()->json($pagos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Pago::class);

        $request->validate([
            'idTicket' => 'required|exists:tickets,idTicket',
            'monto' => 'required|numeric|min:0',
            'metodo' => 'required|string|max:100',
            'estado' => ['required', 'string', Rule::in(['pendiente', 'completado', 'fallido'])],
        ]);

        $ticket = Ticket::find($request->idTicket);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado.'], 404);
        }

        // Opcional: Verificar que el monto coincide con el precio del ticket
        if ($ticket->precio != $request->monto) {
             // return response()->json(['message' => 'El monto del pago no coincide con el precio del ticket.'], 400);
        }

        // Evitar múltiples pagos completados para un mismo ticket
        if ($ticket->pago()->where('estado', 'completado')->exists()) {
             return response()->json(['message' => 'Este ticket ya tiene un pago completado.'], 409);
        }

        $pago = Pago::create($request->all());

        return response()->json($pago, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pago = Pago::with('ticket.evento', 'ticket.asistente')->find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        $this->authorize('view', $pago);

        return response()->json($pago);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pago = Pago::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        $this->authorize('update', $pago);

        $request->validate([
            'monto' => 'sometimes|numeric|min:0',
            'metodo' => 'sometimes|string|max:100',
            'estado' => ['sometimes', 'string', Rule::in(['pendiente', 'completado', 'fallido'])],
        ]);

        $pago->update($request->all());

        return response()->json($pago);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pago = Pago::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        $this->authorize('delete', $pago);

        $pago->delete();

        return response()->json(['message' => 'Pago eliminado correctamente'], 204);
    }

    public function procesarPago(string $id)
    {
        $pago = Pago::find($id);
        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }
        $this->authorize('procesarPago', $pago);

        // Aquí iría la lógica real de procesamiento de pago (ej. pasarela de pago)
        $pago->estado = 'completado';
        $pago->save();

        return response()->json(['message' => 'Pago procesado y completado', 'pago' => $pago]);
    }

    public function verificarPago(string $id)
    {
        $pago = Pago::find($id);
        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }
        $this->authorize('verificarPago', $pago);

        $esCompletado = $pago->estado === 'completado';
        return response()->json(['esCompletado' => $esCompletado, 'pago' => $pago]);
    }
}