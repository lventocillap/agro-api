<?php 

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Controlador de autenticación para gestionar el registro, inicio de sesión y validación de usuarios.
 */
class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario en la base de datos.
     */
    public function registerUser(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:5|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'role' => 'required|string|in:admin,user',
            'password' => 'required|string|min:8'
        ]);

        // Si la validación falla, retorna un error
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Creación del usuario con la contraseña encriptada
        User::create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'role' => $request->get('role'),
            'password' => bcrypt($request->get('password'))
        ]);

        return response()->json([
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * Inicia sesión y devuelve un token JWT si las credenciales son correctas.
     */
    public function loginUser(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:5|max:50',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Obtiene solo las credenciales necesarias
        $credentials = $request->only(['username', 'password']);

        try {
            // Intenta autenticar al usuario y generar un token JWT
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            return response()->json(['token' => $token], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token', $e], 500);
        }
    }   

    /**
     * Obtiene los datos del usuario autenticado mediante el token JWT.
     */
    public function getUser()
    {
        try {
            // Intenta obtener el usuario autenticado a partir del token
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            return response()->json($user, 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token no encontrado'], 401);
        }
    }

    /**
     * Cierra sesión invalidando el token JWT actual.
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
