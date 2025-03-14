<?php

namespace App\Http\Controllers;

use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AboutUsController extends Controller
{
    // Obtiene el único registro de AboutUs, si existe
    public function getAboutUs()
    {
        $aboutUs = AboutUs::first();

        return $aboutUs
            ? response()->json($aboutUs, 200)
            : response()->json(['message' => 'Información no encontrada'], 404);
    }

    // Actualiza los datos de AboutUs por ID
    public function updateAboutUs(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:50',
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'name_yt' => 'nullable|string|max:100',
            'url_yt' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $aboutUs = AboutUs::findOrFail($id);

        // Filtra los datos vacíos antes de actualizar
        $data = array_filter($request->only(['title', 'mission', 'vision', 'name_yt', 'url_yt']), function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($data)) {
            return response()->json(['message' => 'No se enviaron datos válidos para actualizar'], 400);
        }

        $aboutUs->update($data);

        return response()->json([
            'message' => 'Información actualizada correctamente',
            'data' => $aboutUs
        ], 200);
    }

    // Agrega un nuevo valor a la lista de valores de AboutUs
    public function addValueAboutUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255|filled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $aboutUs = AboutUs::firstOrFail();

        // Obtiene la lista actual de valores o inicializa una nueva
        $values = $aboutUs->values ?? [];

        // Agrega el nuevo valor evitando duplicados
        $values[] = $request->input('value');
        $aboutUs->values = array_values(array_unique($values));

        $aboutUs->save();

        return response()->json([
            'message' => 'Valor agregado correctamente',
            'values' => $aboutUs->values
        ]);
    }

    // Modifica un valor existente en la lista de valores
    public function updateValueAboutUs(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'oldValue' => 'required|string|filled',
            'newValue' => 'required|string|max:255|filled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $aboutUs = AboutUs::findOrFail($id);
        $values = $aboutUs->values ?? [];

        if (!in_array($request->input('oldValue'), $values)) {
            return response()->json(['message' => 'El valor a actualizar no fue encontrado'], 404);
        }

        // Reemplaza el valor antiguo con el nuevo
        $values = array_map(
            fn($v) => $v === $request->input('oldValue') ? $request->input('newValue') : $v,
            $values
        );

        $aboutUs->values = $values;
        $aboutUs->save();

        return response()->json(['message' => 'Valor actualizado correctamente', 'values' => $aboutUs->values]);
    }

    // Elimina un valor específico de la lista de valores
    public function deleteValueAboutUs(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255|filled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $aboutUs = AboutUs::findOrFail($id);
        $values = $aboutUs->values ?? [];

        if (!in_array($request->input('value'), $values)) {
            return response()->json(['message' => 'El valor no fue encontrado'], 404);
        }

        // Elimina el valor solicitado y reorganiza la lista
        $aboutUs->values = array_values(array_diff($values, [$request->input('value')]));
        $aboutUs->save();

        return response()->json(['message' => 'Valor eliminado correctamente', 'values' => $aboutUs->values]);
    }
}
