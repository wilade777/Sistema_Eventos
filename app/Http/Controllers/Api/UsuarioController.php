<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Access\AuthorizationException; // Importar para manejar excepciones de autorización

class UsuarioController extends Controller
{
    public function __construct()
    {
        // Aplicar middleware de autenticación a todas las rutas excepto login y store
        $this->middleware('auth:sanctum')->except(['store', 'login']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Usuario::class); // Autorizar la acción viewAny

        // Lógica de filtrado si es necesario para roles específicos
        if (auth()->user()->esAsistente()) {
            return response()->json([auth()->user()]); // Un asistente solo se ve a sí mismo en la lista
        }

        $usuarios = Usuario::all(); // Esto podría filtrarse más según el rol
        return response()->json($usuarios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // La creación de usuarios para registro público NO usa la política 'create' aquí.
        // Si se quiere que solo un admin pueda crear usuarios, esta ruta debería estar protegida.
        // Para el registro público, la validación es suficiente.
        $request->validate([
            'nombre' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuarios',
            'contrasena' => 'required|string|min:8',
            'rol' => ['required', 'string', Rule::in(['Administrador', 'Organizador', 'Secretaria', 'Asistente'])],
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'rol' => $request->rol,
        ]);

        return response()->json($usuario, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $this->authorize('view', $usuario); // Autorizar la acción view para un usuario específico

        return response()->json($usuario);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $this->authorize('update', $usuario); // Autorizar la acción update

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'correo' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('usuarios')->ignore($usuario->idUsuario, 'idUsuario')],
            'contrasena' => 'sometimes|string|min:8',
            'rol' => ['sometimes', 'string', Rule::in(['Administrador', 'Organizador', 'Secretaria', 'Asistente'])],
        ]);

        $usuario->nombre = $request->input('nombre', $usuario->nombre);
        $usuario->correo = $request->input('correo', $usuario->correo);
        if ($request->has('contrasena')) {
            $usuario->contrasena = Hash::make($request->contrasena);
        }
        $usuario->rol = $request->input('rol', $usuario->rol);
        $usuario->save();

        return response()->json($usuario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $this->authorize('delete', $usuario); // Autorizar la acción delete

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente'], 204);
    }

    // AQUI COMIENZA EL METODO LOGIN QUE DEBES COPIAR Y PEGAR
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required',
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();

        // Verificar si el usuario existe y si la contraseña es correcta
        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Crear un token de acceso personal para el usuario
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'usuario' => $usuario, // Puedes devolver el objeto usuario o un recurso de usuario
        ]);
    }
    // AQUI TERMINA EL METODO LOGIN

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete(); // Elimina el token actual
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}