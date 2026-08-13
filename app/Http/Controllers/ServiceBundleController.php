<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceBundleRequest;
use App\Http\Requests\UpdateServiceBundleRequest;
use App\Models\ServiceBundle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceBundleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceBundle::with('services');

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $bundles = $query->latest()->paginate($request->query('per_page', 15));

        return $this->success($bundles);
    }

    public function store(StoreServiceBundleRequest $request): JsonResponse
    {
        $bundle = ServiceBundle::create($request->only(['name', 'description']));

        if ($request->has('services')) {
            $bundle->services()->sync($request->input('services'));
        }

        $bundle->load('services');

        return $this->success($bundle, 'Service bundle created successfully', 201);
    }

    public function show(ServiceBundle $bundle): JsonResponse
    {
        $bundle->load('services');

        return $this->success($bundle);
    }

    public function update(UpdateServiceBundleRequest $request, ServiceBundle $bundle): JsonResponse
    {
        $bundle->update($request->only(['name', 'description']));

        if ($request->has('services')) {
            $bundle->services()->sync($request->input('services'));
        }

        $bundle->load('services');

        return $this->success($bundle, 'Service bundle updated successfully');
    }

    public function destroy(ServiceBundle $bundle): JsonResponse
    {
        $bundle->delete();

        return $this->success(null, 'Service bundle deleted successfully');
    }
}
