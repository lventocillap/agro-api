<?php 


namespace App\Http\Controllers\AuthUsers;

use App\Http\Controllers\Controller; // Agrega esta línea
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(name="Authentication", 
 * description="Endpoints de autenticación de usuarios")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registra un nuevo usuario",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "role", "password"},
     *             @OA\Property(property="username", type="string", example="usuario123"),
     *             @OA\Property(property="email", type="string", example="usuario@example.com"),
     *             @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user"),
     *             @OA\Property(property="password", type="string", format="password", example="securePassword123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuario registrado correctamente"),
     *     @OA\Response(response=422, description="Error en validación de datos")
     * )
     */
    public function registerUser(Request $request)
    {
        // Verifica si ya existe un usuario en la tabla
        $userExists = User::exists();

        if ($userExists) {
            return response()->json(['error' => 'El registro está bloqueado. Ya existe un administrador.'], 403);
        }

        // Validación de datos
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:5|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Crear el único usuario admin
        User::create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'role' => 'admin', 
            'password' => bcrypt($request->get('password'))
        ]);

        return response()->json(['message' => 'Usuario admin creado con éxito'], 201);
    }



    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Inicia sesión y obtiene un token JWT",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="usuario123"),
     *             @OA\Property(property="password", type="string", format="password", example="securePassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Autenticación exitosa, devuelve el token JWT"),
     *     @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */
    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:5|max:50',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only(['username', 'password']);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            return response()->json(['token' => $token], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token', $e], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Obtiene los datos del usuario autenticado",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Datos del usuario autenticado"),
     *     @OA\Response(response=401, description="Token inválido o expirado")
     * )
     */
    public function getUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            return response()->json($user, 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token error'], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Cierra sesión e invalida el token JWT",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Cierre de sesión exitoso")
     * )
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
