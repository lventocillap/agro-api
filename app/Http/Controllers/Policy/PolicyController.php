<?php

namespace App\Http\Controllers\Policy;

use App\Http\Requests\Policy\ValidatePolicyUpdate;
use App\Http\Controllers\Controller;
use App\Http\Service\Image\SaveImageService;
use App\Models\Policies;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    use ValidatePolicyUpdate, SaveImageService;

    public function getPolicy() : JsonResponse
    {
        $policies = Policies::with('image')->first();
        return new JsonResponse($policies, 200);
    }

    public function updatePolicy(Request $request) : JsonResponse
    {
        $policy = Policies::first();
        $validateData = $this->validatePolicyUpdate($request);
        
        // Si hay imagen en la solicitud
        if ($request->has('image') && !empty($request->image)) {
            try {
                $existingImage = $policy->image()->latest()->first();
                if ($existingImage) {
                    // Eliminar la imagen anterior del almacenamiento
                    $this->deleteImage($existingImage->url);
                }
                $image = $this->saveImageBase64($request->image, 'policies');
                
                if ($policy->image) {
                    // Si la imagen ya existe, actualizamos
                    $policy->image()->update(['url' => $image]);
                } else {
                    // Si no hay imagen, creamos una nueva relación
                    $policy->image()->create(['url' => $image]);
                }
                $policy->load('image');
            } catch (\Exception $e) {
                return new JsonResponse([
                    'message' => 'Error saving image',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        
        $policy->update($validateData);

        return new JsonResponse([
            'message' => 'Policy updated successfully',
            'policy' => $policy
        ], 200);
    }

    /* public function updateImageToAboutUs(Request $request)
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
    } */

}   