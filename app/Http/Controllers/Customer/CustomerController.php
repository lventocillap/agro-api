<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\Customer\NotFoundCustomer;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllCustomers(Request $request): JsonResponse
    {
        $nameCustomer = $request->query('customer');
        $active = $request->query('active');
        $limit = $request->query('limit', 10);

        $customers = Customer::select('id', 'email', 'active')
        ->when($nameCustomer, fn($query) =>
            $query->where('email', 'like', "%{$nameCustomer}%")
        )
        ->when(isset($active), fn($query) =>
            $query->where('active', $active) 
        )
        ->orderByDesc('id')
        ->paginate($limit);
    

        return response()->json([
            'data'         => $customers->items(),
            'current_page' => $customers->currentPage(),
            'total'        => $customers->total(),
            'last_page'    => $customers->lastPage(),
            'next_page'    => $customers->nextPageUrl(),
            'prev_page'    => $customers->previousPageUrl()
        ]);
    }

    /**
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getCustomer(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new NotFoundCustomer;
        }

        return response()->json(['data' => $customer]);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'  => 'required|email|unique:customers,email',
            'active' => 'required|boolean'
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer created successfully',
            'data'    => $customer
        ], 201);
    }

    /**
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new NotFoundCustomer;
        }

        $validated = $request->validate([
            'email'  => 'required|email|unique:customers,email,' . $id,
            'active' => 'required|boolean'
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully',
            'data'    => $customer
        ]);
    }

    /**
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new NotFoundCustomer;
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
