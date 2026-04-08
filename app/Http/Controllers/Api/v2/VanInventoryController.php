<?php

namespace App\Http\Controllers\Api\v2;
use App\Actions\VanInventory\ListVanInventoryAction;
use App\Actions\VanInventory\CreateVanInventoryAction;
use App\Actions\VanInventory\UpdateVanInventoryAction;
use App\Actions\VanInventory\DeleteVanInventoryAction;
use App\Actions\Van\FetchRecentActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\VanInventory\StoreVanInventoryRequest;
use App\Http\Requests\VanInventory\UpdateVanInventoryRequest;
use App\Models\VanInventory;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VanInventoryController extends Controller
{
    /**
     * Display a listing of Van Inventory
     */

    //     public function loginVanInventory(Request $request)
    // {
        
    //     $credentials = $request->only(['email', 'password']);

    //     if (!$token = auth('van_inventory')->attempt($credentials)) {
    //         return JsonResponseServices::getApiResponse([], false, 'Invalid credentials', 401);
    //     }

    //     return JsonResponseServices::getApiResponse([
    //         'access_token' => $token,
    //         'token_type' => 'bearer',
    //         'expires_in' => auth('van_inventory')->factory()->getTTL() * 60
    //     ], true, 'Login successful', 200);
    // }

    public function index(ListVanInventoryAction $action): JsonResponse
    {
        $data = $action->execute();

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Store a newly created Van Inventory
     */

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);
        $item = VanInventory::create($validated);
        return JsonResponseServices::getApiResponse(
            $item,
            config('constants.TRUE_STATUS'),
            'Inventory created successfully',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Display the specified Van Inventory
     */
    public function show($id, ListVanInventoryAction $action): JsonResponse
    {
        $data = $action->execute(['id' => $id]);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Update the specified Van Inventory
     */
    public function update($id, UpdateVanInventoryRequest $request, UpdateVanInventoryAction $action): JsonResponse
    {
        $validatedData = (object) $request->validated();

        $data = $action->execute($id, $validatedData);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            'Van Inventory updated successfully',
            config('constants.HTTP_OK')
        );
    }

    /**
     * Remove the specified Van Inventory
     */
    public function destroy($id, DeleteVanInventoryAction $action): JsonResponse
    {
        $data = $action->execute($id);

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            'Van Inventory deleted successfully',
            config('constants.HTTP_OK')
        );
    }
}