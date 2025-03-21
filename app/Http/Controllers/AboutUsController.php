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

/**
 * @OA\Tag(
 *     name="AboutUs",
 *     description="Endpoints para gestionar la información de About Us"
 * )
 */
class AboutUsController extends Controller
{
    use SaveImageAboutUs;
    use ValidateAboutUs;

    /**
     * @OA\Get(
     *     path="/api/about-us",
     *     summary="Obtener información de About Us",
     *     tags={"AboutUs"},
     *     @OA\Response(response=200, description="Información obtenida correctamente"),
     *     @OA\Response(response=404, description="Registro no encontrado"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/about-us",
     *     summary="Actualizar información de About Us",
     *     tags={"AboutUs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="mission", type="string"),
     *             @OA\Property(property="vision", type="string"),
     *             @OA\Property(property="name_yt", type="string"),
     *             @OA\Property(property="url_yt", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Información actualizada correctamente")
     * )
     */
    public function updateAboutUs(Request $request)
    {
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

    /**
     * @OA\Post(
     *     path="/api/about-us/image",
     *     summary="Crea o Actualiza una imagen de About Us",
     *     tags={"AboutUs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="image", type="string", description="Imagen en base64")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Imagen actualizada con éxito"),
     *     @OA\Response(response=500, description="Error al actualizar la imagen")
     * )
     */
    public function updateImageToAboutUs(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|string',
            ]);

            $aboutUs = AboutUs::firstOrCreate([]);

            DB::transaction(function () use ($aboutUs, $request) {
                $existingImage = $aboutUs->images()->latest()->first();
                $imagePath = $this->saveImageBase64($request->image, 'about_us_images');

                if (!$imagePath) {
                    throw new \Exception("Error al guardar la imagen.");
                }

                if ($existingImage) {
                    $this->deleteImage($existingImage->url);
                    $existingImage->update(['url' => $imagePath]);
                } else {
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

    /**
     * @OA\Post(
     *     path="/api/about-us/{id}/value",
     *     summary="Agregar un nuevo valor a About Us",
     *     tags={"AboutUs"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="about_values", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Valor agregado con éxito")
     * )
     */
    public function addValueAboutUs(Request $request, $id)
    {
        $request->validate([
            'about_values' => 'required|string|max:255',
        ]);

        $aboutUs = AboutUs::find($id);
        
        if (!$aboutUs) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $values = $aboutUs->about_values ?? [];

        if (in_array($request->about_values, $values)) {
            return response()->json(['message' => 'Este valor ya existe'], 422);
        }

        $values[] = $request->about_values;
        $aboutUs->about_values = $values;
        $aboutUs->save();

        return response()->json([
            'message' => 'Valor agregado con éxito',
            'values' => $aboutUs->about_values,
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/about-us/{id}/value",
     *     summary="Actualizar un valor existente en About Us",
     *     tags={"AboutUs"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="oldAboutValue", type="string"),
     *             @OA\Property(property="newAboutValue", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Valor actualizado correctamente")
     * )
     */
    public function updateValueAboutUs(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'oldAboutValue' => 'required|string|max:255',
            'newAboutValue' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $aboutUs = AboutUs::find($id);
        if (!$aboutUs) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $about_values = $aboutUs->about_values ?? [];

        if (!in_array($request->oldAboutValue, $about_values)) {
            return response()->json(['message' => 'El valor a actualizar no fue encontrado'], 404);
        }

        $updated_about_values = array_map(fn($v) => $v === $request->oldAboutValue ? $request->newAboutValue : $v, $about_values);

        $aboutUs->about_values = $updated_about_values;
        $aboutUs->save();

        return response()->json([
            'message' => 'Valor actualizado correctamente',
            'about_values' => $updated_about_values
        ], 200);
    }
}

