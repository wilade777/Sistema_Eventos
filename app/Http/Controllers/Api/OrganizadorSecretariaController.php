<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Asegúrate de que esta línea esté presente

class OrganizadorSecretariaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Asignar una secretaria a un organizador.
     */
    public function assignSecretaria(Request $request, string $idOrganizador)
    {
        $organizador = Usuario::where('idUsuario', $idOrganizador)->where('rol', 'Organizador')->first();
        if (!$organizador) {
            return response()->json(['message' => 'Organizador no encontrado o rol incorrecto.'], 404);
        }

        // Política: Solo un administrador o el propio organizador pueden asignar secretarias
        // La política 'before' en EventoPolicy ya manejará al administrador globalmente.
        if (!auth()->user()->esAdministrador() && auth()->user()->idUsuario !== $organizador->idUsuario) {
            return response()->json(['message' => 'No autorizado para asignar secretarias a este organizador.'], 403);
        }

        $request->validate([
            'idSecretaria' => ['required', Rule::exists('usuarios', 'idUsuario')->where(function ($query) {
                $query->where('rol', 'Secretaria');
            })],
        ]);

        $secretaria = Usuario::find($request->idSecretaria);
        if (!$secretaria || !$secretaria->esSecretaria()) {
            return response()->json(['message' => 'El ID proporcionado no corresponde a una secretaria válida.'], 400);
        }

        // Adjuntar la secretaria al organizador
        $organizador->secretariasAsignadas()->syncWithoutDetaching([$secretaria->idUsuario]);

        return response()->json(['message' => 'Secretaria asignada correctamente al organizador.', 'organizador' => $organizador->load('secretariasAsignadas')]);
    }

    /**
     * Desasignar una secretaria de un organizador.
     */
    public function unassignSecretaria(Request $request, string $idOrganizador)
    {
        $organizador = Usuario::where('idUsuario', $idOrganizador)->where('rol', 'Organizador')->first();
        if (!$organizador) {
            return response()->json(['message' => 'Organizador no encontrado o rol incorrecto.'], 404);
        }

        // Política: Solo un administrador o el propio organizador pueden desasignar secretarias
        if (!auth()->user()->esAdministrador() && auth()->user()->idUsuario !== $organizador->idUsuario) {
            return response()->json(['message' => 'No autorizado para desasignar secretarias de este organizador.'], 403);
        }

        $request->validate([
            // TEMPORALMENTE SIMPLIFICADO PARA DEPURACIÓN
            'idSecretaria' => 'required|exists:usuarios,idUsuario', // <--- CAMBIO AQUÍ
        ]);

        $secretaria = Usuario::find($request->idSecretaria);
        if (!$secretaria || !$secretaria->esSecretaria()) {
            return response()->json(['message' => 'El ID proporcionado no corresponde a una secretaria válida.'], 400);
        }

        // Desvincular la secretaria del organizador
        $organizador->secretariasAsignadas()->detach($secretaria->idUsuario);

        return response()->json(['message' => 'Secretaria desasignada correctamente del organizador.', 'organizador' => $organizador->load('secretariasAsignadas')]);
    }

    /**
     * Obtener las secretarias asignadas a un organizador.
     */
    public function getSecretarias(string $idOrganizador)
    {
        $organizador = Usuario::where('idUsuario', $idOrganizador)->where('rol', 'Organizador')->first();
        if (!$organizador) {
            return response()->json(['message' => 'Organizador no encontrado o rol incorrecto.'], 404);
        }

        // Política: Solo un administrador o el propio organizador pueden ver sus secretarias
        if (!auth()->user()->esAdministrador() && auth()->user()->idUsuario !== $organizador->idUsuario) {
            return response()->json(['message' => 'No autorizado para ver las secretarias de este organizador.'], 403);
        }

        return response()->json($organizador->load('secretariasAsignadas')->secretariasAsignadas);
    }
}