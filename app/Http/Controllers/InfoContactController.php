<?php

namespace App\Http\Controllers;

use App\Models\InfoContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InfoContactController extends Controller
{
    // Obtener el único registro de información de contacto
    public function getInfoContact()
    {
        $infoContact = InfoContact::first();

        return $infoContact
            ? response()->json($infoContact, 200)
            : response()->json(['message' => 'Información no encontrada'], 404);
    }

    // Actualizar la información de contacto
    public function updateInfoContact(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string|max:100',
            'cellphone' => 'nullable|string|size:9',
            'email' => 'nullable|email|max:320',
            'attention_hours' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar el registro por ID o devolver error 404
        $infoContact = InfoContact::findOrFail($id);

        // Filtrar campos vacíos antes de actualizar
        $data = array_filter($request->only(['location', 'cellphone', 'email', 'attention_hours']), function ($value) {
            return $value !== null && $value !== '';
        });

        // Si no hay datos válidos, evitar actualización innecesaria
        if (empty($data)) {
            return response()->json(['message' => 'No se enviaron datos válidos para actualizar.'], 400);
        }

        // Actualizar solo los campos proporcionados
        $infoContact->update($data);

        return response()->json([
            'message' => 'Información de contacto actualizada correctamente',
            'data' => $infoContact
        ], 200);
    }
}