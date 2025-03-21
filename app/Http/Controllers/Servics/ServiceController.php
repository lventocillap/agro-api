<?php

namespace App\Http\Controllers\Servics;

use App\Exceptions\Servics\NotFoundFeature;
use App\Exceptions\Servics\NotFoundService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Servics\ValidateServiceStore;
use App\Http\Service\Image\SaveImageService;
use App\Models\Service;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ServiceController extends Controller
{

    use SaveImageService, ValidateServiceStore;

    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Obtener todos los servicios",
     *     tags={"Services"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de servicios obtenida correctamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Service")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No hay servicios para mostrar",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="There are no services to display")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function getServices() : JsonResponse
    {
        try{
            $services = Service::with('image')->get();
            if($services->isEmpty()){
                return new JsonResponse(['message' => 'There are no services to display'], 404);
            }
            // Convertir features de string a array para cada servicio
            $services->transform(function ($service) {
                $service->features = $service->features ? explode('益', $service->features) : [];
                return $service;
            });
            return new JsonResponse($services, 200);
        } catch (\Exception $e) {
            throw NotFoundService::serviceLoadError(); 
        }
    }

    /**
     * @OA\Post(
     *     path="/api/services",
     *     summary="Crear un nuevo servicio",
     *     security={{"bearerAuth": {}}},
     *     tags={"Services"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description"},
     *             @OA\Property(property="title", type="string", example="Almacenamiento"),
     *             @OA\Property(property="description", type="string", example="Se ofrece el servicio de almacenamiento de mercancía"),
     *             @OA\Property(property="features", type="array", @OA\Items(type="string"), example={"Rapido", "Económico"}),
     *             @OA\Property(property="image", type="string", format="base64", example="data:image/png;base64,iVBORw0KGgoAAAANS...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Servicio creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service created successfully"),
     *             @OA\Property(property="service", ref="#/components/schemas/Service")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The title field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function createService(Request $request) : JsonResponse
    {
        $validatedData = $this->validateServiceStore($request);
        

        // Guardar el feature si fue enviado
        if ($request->has('features') && !empty($request->features)) {
            $validatedData['features'] = implode('益', $request->features);
        }

        $service = Service::create($validatedData);

        // Guardar la imagen solo si fue enviada
        if ($request->has('image') && !empty($request->image)) {
            $image = $this->saveImageBase64($request->image, 'services');
    
            if ($image) { // Asegurar que no sea null
                $service->image()->create(['url' => $image]);
                $service->load('image'); // Recargar la relación para que no devuelva null
            }
        }
        // Convertir features de string a array antes de devolver la respuesta
        $service->features = $service->features ? explode('益', $service->features) : [];

        return new JsonResponse([
            'message' => 'Service created successfully',
            'service' => $service
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/services/{id}",
     *     summary="Obtener un servicio por ID",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del servicio a obtener",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del servicio obtenido",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servicio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     )
     * )
     */
    public function getServiceById($id) : JsonResponse
    {
        try {
            $service = Service::with('image')->findOrFail($id);
    
            // Convertir features de string a array antes de devolver la respuesta
            $service->features = $service->features ? explode('益', $service->features) : [];
    
            return new JsonResponse($service, 200);
        } catch (Exception $e) {
            throw new NotFoundService();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/services/{id}",
     *     summary="Actualizar un servicio por ID",
     *     security={{"bearerAuth": {}}},
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del servicio a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description"},
     *             @OA\Property(property="title", type="string", example="Nuevo título"),
     *             @OA\Property(property="description", type="string", example="Nueva descripción"),
     *             @OA\Property(
     *                 property="features",
     *                 type="array",
     *                 @OA\Items(type="string", example="Feature 1")
     *             ),
     *             @OA\Property(property="image", type="string", format="byte", example="base64_encoded_string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servicio actualizado correctamente",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servicio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     )
     * )
     */
    public function updateServiceById(Request $request, $id) : JsonResponse
    {
        try {
            $service = Service::findOrFail($id); // Usa findOrFail para lanzar excepción si no existe
    
            // Validamos los datos de actualización
            $validatedData = $this->validateServiceUpdate($request);
            
            // Guardar el feature si fue enviado
            if ($request->has('features') && !empty($request->features)) {
                $validatedData['features'] = implode('益', $request->features);
            }

            // Si hay imagen en la solicitud
            if ($request->has('image') && !empty($request->image)) {
                try {
                    $existingImage = $service->image()->latest()->first();
                    if ($existingImage) {
                        // Eliminar la imagen anterior del almacenamiento
                        $this->deleteImage($existingImage->url);
                    }
                    $image = $this->saveImageBase64($request->image, 'services');
    
                    if ($service->image) {
                        // Si la imagen ya existe, actualizamos
                        $service->image()->update(['url' => $image]);
                    } else {
                        // Si no hay imagen, creamos una nueva relación
                        $service->image()->create(['url' => $image]);
                    }
                    // Recargar la relación para que no devuelva null
                    $service->load('image');
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'message' => 'Error saving image',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
    
            // Actualizamos los datos del servicio
            $service->update($validatedData);
            $service->save();

            // Convertir features de string a array antes de devolver la respuesta
            $service->features = $service->features ? explode('益', $service->features) : [];

            return new JsonResponse([
                'message' => 'Service updated successfully',
                'service' => $service
            ], 200);
        } catch (Exception $e) {
            throw new NotFoundService();
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    /**
     * @OA\Delete(
     *     path="/api/services/{id}",
     *     summary="Eliminar un servicio por ID",
     *     security={{"bearerAuth": {}}},
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del servicio a eliminar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servicio eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servicio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     )
     * )
     */
    public function deleteService($id) : JsonResponse
    {
        $service = Service::with('image')->find($id);
        
        if (!$service) {
            throw new NotFoundService();
        }

        $existingImage = $service->image;
        if ($existingImage) {
        // Eliminar la imagen anterior del almacenamiento
        $this->deleteImage($existingImage->url);
        // Eliminar la imagen de la base de datos
        $existingImage->delete();
        }

        $service->delete();

        return new JsonResponse(['message' => 'Service deleted successfully'], 200);
    }
}