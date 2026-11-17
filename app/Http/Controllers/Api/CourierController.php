<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourierController extends Controller
{
    /**
     * Display a paginated listing of couriers with search, level filter, and sorting.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $sortBy = $request->query('sort_by', $request->query('sort'));
        $sortDir = $request->query('sort_dir', $request->query('order'));

        $couriers = Courier::query()
            ->search($request->query('search'))
            ->levels($request->query('level'))
            ->sortedBy(
                is_string($sortBy) ? $sortBy : null,
                is_string($sortDir) ? $sortDir : null,
            )
            ->paginate($perPage)
            ->withQueryString();

        return CourierResource::collection($couriers);
    }

    /**
     * Store a newly created courier in storage.
     */
    public function store(StoreCourierRequest $request): JsonResponse
    {
        $courier = Courier::create($request->validated());

        return (new CourierResource($courier))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified courier with all attributes.
     */
    public function show(Courier $courier): CourierResource
    {
        return new CourierResource($courier);
    }

    /**
     * Update the specified courier in storage.
     */
    public function update(UpdateCourierRequest $request, Courier $courier): CourierResource
    {
        $courier->update($request->validated());

        return new CourierResource($courier->fresh());
    }

    /**
     * Remove the specified courier from storage.
     */
    public function destroy(Courier $courier): JsonResponse
    {
        $courier->delete();

        return response()->json([
            'message' => 'Courier deleted successfully.',
        ]);
    }
}
