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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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