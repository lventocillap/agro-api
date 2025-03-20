<?php

namespace App\Http\Controllers;

use App\Exceptions\AboutUs\NotFoundAboutUs; 
use App\Http\Service\Image\SaveImageAboutUs;
use App\Http\Requests\AboutUs\ValidateAboutUs;
use App\Models\AboutUs;
use Illuminate\Support\Facades\DB;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


/**
 * Controlador de About-Us para mostrar y actualizar, tambien para gestionar los valores como, crear, mostrar, actualizar, eliminar y creacion de imagenes.
 */
class AboutUsController extends Controller
{
    use SaveImageAboutUs;
    use ValidateAboutUs;
    
    // Obtiene el único registro de AboutUs, si existe
    public function getAboutUs() 
    {
        try {
            $aboutUs = AboutUs::with('images')->first();

            if (!$aboutUs) {
                throw new NotFoundAboutUs();
            }

            if ($aboutUs->images) {
                $aboutUs->images->each(function ($image) {
                    $image->url = asset('storage/' . basename($image->url));
                });
            }

            return response()->json($aboutUs, 200);

        } catch (NotFoundAboutUs $e) {
            return $e->render();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener About Us: ' . $e->getMessage()], 500);
        }
    }

    // Actualiza los datos de AboutUs
    public function updateAboutUs(Request $request)
    {
        // Llamamos al método de validación del trait
        $this->validateAboutUs($request);

        $aboutUs = AboutUs::firstOrCreate([]);
        $data = array_filter($request->only(['mission', 'vision', 'name_yt', 'url_yt']), function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($data)) {
            $aboutUs->update($data);
        }

        return response()->json([
            'message' => 'Información actualizada correctamente',
            'data' => $aboutUs
        ], 200);
    }

    public function updateImageToAboutUs(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|string',
            ]);

            $aboutUs = AboutUs::firstOrCreate([]);

            DB::transaction(function () use ($aboutUs, $request) {
                // Obtener la imagen actual
                $existingImage = $aboutUs->images()->latest()->first();

                // Guardar la nueva imagen
                $imagePath = $this->saveImageBase64($request->image, 'about_us_images');

                if (!$imagePath) {
                    throw new \Exception("Error al guardar la imagen.");
                }

                if ($existingImage) {
                    // Eliminar la imagen anterior del almacenamiento
                    $this->deleteImage($existingImage->url);

                    // Actualizar la URL en la base de datos en lugar de eliminar el registro
                    $existingImage->update(['url' => $imagePath]);
                } else {
                    // Si no hay imagen previa, crear un nuevo registro
                    $aboutUs->images()->create(['url' => $imagePath]);
                }
            });

            return response()->json([
                'message' => 'Imagen actualizada con éxito',
                'path' => asset('storage/' . $aboutUs->images()->latest()->first()->url),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la imagen: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Agrega un nuevo valor a la lista de valores de AboutUs
    public function addValueAboutUs(Request $request, $id)
    {
        // Validar que 'about_values' viene en la petición
        $request->validate([
            'about_values' => 'required|string|max:255',
        ]);

        // Buscar el registro AboutUs
        $aboutUs = AboutUs::find($id);
        
        if (!$aboutUs) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        // Asegurar que about_values es un array
        $values = $aboutUs->about_values ?? [];

        // Verificar que el nuevo valor no esté duplicado
        if (in_array($request->about_values, $values)) {
            return response()->json(['message' => 'Este valor ya existe'], 422);
        }

        // Agregar el nuevo valor al array
        $values[] = $request->about_values;
        $aboutUs->about_values = $values; // Laravel lo guarda como JSON automáticamente
        $aboutUs->save();

        return response()->json([
            'message' => 'Valor agregado con éxito',
            'values' => $aboutUs->about_values, // Laravel ya lo devolverá como array
        ], 200);
    }

    // Modifica un valor existente en la lista de valores
    public function updateValueAboutUs(Request $request, $id)
    {
        // Validar que oldValue y newValue sean strings válidos
        $validator = Validator::make($request->all(), [
            'oldAboutValue' => 'required|string|max:255',
            'newAboutValue' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar el registro AboutUs
        $aboutUs = AboutUs::find($id);
        if (!$aboutUs) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        // Obtener el array de about_values asegurando que no sea null
        $about_values = $aboutUs->about_values ?? [];

        // Verificar si el valor antiguo existe en el array
        if (!in_array($request->oldAboutValue, $about_values)) {
            return response()->json(['message' => 'El valor a actualizar no fue encontrado'], 404);
        }

        // Reemplazar el valor antiguo con el nuevo
        $updated_about_values = array_map(fn($v) => $v === $request->oldAboutValue ? $request->newAboutValue : $v, $about_values);

        // Guardar el array modificado
        $aboutUs->about_values = $updated_about_values;
        $aboutUs->save();

        return response()->json([
            'message' => 'Valor actualizado correctamente',
            'about_values' => $updated_about_values
        ], 200);
    }

    public function deleteValueAboutUs(Request $request, $id)
    {
        // Validar que 'aboutValue' viene en la petición
        $validator = Validator::make($request->all(), [
            'aboutValue' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar el registro AboutUs
        $aboutUs = AboutUs::find($id);
        if (!$aboutUs) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        // Obtener el array de about_values asegurando que no sea null
        $about_values = $aboutUs->about_values ?? [];

        // Verificar si el valor existe en la lista
        if (!in_array($request->aboutValue, $about_values)) {
            return response()->json(['message' => 'El valor no fue encontrado'], 404);
        }

        // Eliminar el valor y reorganizar la lista
        $updated_about_values = array_values(array_diff($about_values, [$request->aboutValue]));

        // Guardar el array modificado
        $aboutUs->about_values = $updated_about_values;
        $aboutUs->save();

        return response()->json([
            'message' => 'Valor eliminado correctamente',
            'about_values' => $updated_about_values
        ], 200);
    }
}
