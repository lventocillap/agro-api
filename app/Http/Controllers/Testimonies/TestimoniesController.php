<?php

namespace App\Http\Controllers\Testimonies;

use App\Exceptions\Testimonie\NotFoundTestimonie;
use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonies\ValidateTestimoniesRequest;
use App\Models\Testimonie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimoniesController extends Controller
{
    use ValidateTestimoniesRequest;

    public function storeTestimonies(Request $request): JsonResponse
    {
        $this->validateTestimoniesRequest($request);

        Testimonie::create([
            'name_customer' => $request->name_customer,
            'description' => $request->description,
            'date' => $request->date,
            'qualification' => $request->qualification
        ]);

        return new JsonResponse(['data' => 'Testimonio registrado']);
    }

    public function updateTestimonies(int $testimonieId, Request $request): JsonResponse
    {
        $testimonie = Testimonie::find($testimonieId);
        if(!$testimonie){
            throw new NotFoundTestimonie;
        }
        $testimonie->update([
            'name_customer' => $request->name_customer,
            'description' => $request->description,
            'date' => $request->date,
            'qualification' => $request->qualification
        ]);
        return new JsonResponse(['data' => 'Testimonio actulizado']);
    }

    public function deleteTestimonie(int $testimonieId): JsonResponse
    {   
        $testimonie = Testimonie::find($testimonieId);
        if(!$testimonie){
            throw new NotFoundTestimonie;
        }
        $testimonie->delete();
        return new JsonResponse(['data' => 'Testiminio eliminado']);
    }

    public function getAllTestimonies(Request $request): JsonResponse
    {
        $nameClient = $request->query('customer');
        $testimonies = Testimonie::select(
            'id', 
            'name_customer', 
            'description', 
            'qualification')
        ->where('name_customer', 'like', "%{$nameClient}%")
        ->get();
        return new JsonResponse(['data' => $testimonies]);
    }
}
