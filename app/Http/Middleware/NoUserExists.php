<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class NoUserExists
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si ya existe un usuario en la base de datos
        if (User::exists()) {
            return response()->json(['error' => 'El registro está bloqueado. Ya existe un administrador.'], 403);
        }

        return $next($request);
    }
}
