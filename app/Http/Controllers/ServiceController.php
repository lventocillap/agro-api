<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Service;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getServices()
    {
        $services = Service::all();
        if($services->isEmpty()){
            return response()->json(['message' => 'No services found'], 404);
        }
        return response()->json($services, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createService(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'required|string|min:5'
        ]);
    
        $service = Service::create($validatedData);
    
        return response()->json([
            'message' => 'Service created successfully',
            'service' => $service
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function getServiceById($id)
    {
        $service = Service::find($id);
    
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        return response()->json($service, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateServiceById(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|string|min:3',
            'description' => 'sometimes|string|min:5'
        ]);

        $service->update($validatedData);

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteService($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $service->delete();

        return response()->json(['message' => 'Service deleted successfully'], 200);
    }

    // Métodos para manejar los features de un servicio

    public function addFeature(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
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

        return response()->json([
            'message' => 'Feature added successfully',
            'service' => $service
        ], 200);
    }

    public function updateFeature(Request $request, $service_id, $feature_id)
    {
        $service = Service::find($service_id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|min:3'
        ]);

        $features = $service->features ?? [];

        if (!isset($features[$feature_id])) {
            return response()->json(['message' => 'Feature not found'], 404);
        }

        // Actualizar el feature en la posición indicada
        $features[$feature_id] = $request->input('name');

        // Guardar los cambios
        $service->features = $features;
        $service->save();

        return response()->json([
            'message' => 'Feature updated successfully',
            'service' => $service
        ], 200);
    }

    public function deleteFeature($id, $index)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $features = $service->features ?? [];

        if (!isset($features[$index])) {
            return response()->json(['message' => 'Feature not found'], 404);
        }

        // Eliminar el feature de la lista
        array_splice($features, $index, 1);

        // Guardar los cambios
        $service->features = $features;
        $service->save();

        return response()->json([
            'message' => 'Feature deleted successfully',
            'service' => $service
        ], 200);
    }
}
