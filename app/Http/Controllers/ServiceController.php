<?php

namespace App\Http\Controllers;

use App\Exceptions\Servics\NotFoundFeature;
use App\Exceptions\Servics\NotFoundService;
use App\Http\Requests\Servics\ValidateServiceStore;
use App\Http\Service\Image\SaveImageService;
use App\Models\Service;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use SaveImageService, ValidateServiceStore;

    public function getServices() : JsonResponse
    {
        try{
            $services = Service::all();
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
    
        $service = Service::create($validatedData);

        // Guardar la imagen y asociarla al servicio
        $image = $this->saveImageBase64($request->image, 'services');
            $service->image()->create([
                'url' => $image
            ]);

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
        $service = Service::with('image')->find($id);
    
        if (!$service) {
            throw new NotFoundService();
        }

        return new JsonResponse($service, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateServiceById(Request $request, $id) : JsonResponse
    {
        $service = Service::find($id);
        if (!$service) {
            throw new NotFoundService();
        }

        $validatedData = $this->validateServiceStore($request);

        $image = $this->saveImageBase64($request->image, 'services');
        $service->update($validatedData);

        $service->image()->update([
            'url' => $image
        ]);

        return new JsonResponse([
            'message' => 'Service updated successfully',
            'service' => $service
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteService($id) : JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            throw new NotFoundService();
        }

        $service->delete();

        return new JsonResponse(['message' => 'Service deleted successfully'], 200);
    }

    // Métodos para manejar los features de un servicio

    public function addFeature(Request $request, $id) : JsonResponse
    {
        $service = Service::find($id);
        if (!$service) {
            throw new NotFoundService();
        }

        $request->validate([
            'name' => 'required|string|min:3'
        ]);

        // Obtener los features actuales (si es null, inicializar como un array vacío)
        $features = $service->features ?? [];

        // Agregar el nuevo feature
        $features[] = $request->input('name');

        // Guardar el cambio en la base de datos
        $service->features = $features;
        $service->save();

        return new JsonResponse([
            'message' => 'Feature added successfully',
            'service' => $service
        ], 201);
    }

    public function updateFeature(Request $request, $id, $index) : JsonResponse
    {
        // Obtener el servicio que contiene la característica a actualizar
        $service = Service::find($id);
        if (!$service) {
            throw new NotFoundService();
        }

        $request->validate([
            'name' => 'required|string|min:3'
        ]);

        $features = $service->features ?? [];

        if (!isset($features[$index])) {
            throw new NotFoundFeature();
        }

        // Actualizar el feature en la posición indicada
        $features[$index] = $request->input('name');

        // Guardar los cambios
        $service->features = $features;
        $service->save();

        return new JsonResponse([
            'message' => 'Feature updated successfully',
            'service' => $service
        ], 200);
    }

    public function deleteFeature($id,$index) : JsonResponse
    {
        // Obtener el servicio que contiene la característica a eliminar
        $service = Service::find($id);
        if (!$service) {
            throw new NotFoundService();
        }

        $features = $service->features ?? [];

        if (!isset($features[$index])) {
            throw new NotFoundFeature();
        }

        // Eliminar el feature de la lista
        array_splice($features, $index, 1);

        // Guardar los cambios
        $service->features = $features;
        $service->save();

        return new JsonResponse([
            'message' => 'Feature deleted successfully',
            'service' => $service
        ], 200);
    }
}
