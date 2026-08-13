<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $query = Service::query();

    if ($request->has("search")) {
      $search = $request->query("search");
      $query->where("name", "like", "%{$search}%");
    }

    $services = $query->latest()->paginate($request->query("per_page", 15));

    return $this->success($services);
  }

  /**
   * @param \App\Http\Requests\StoreServiceRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(StoreServiceRequest $request): JsonResponse
  {
    $service = Service::create($request->validated());

    return $this->success($service, "Service created successfully", 201);
  }

  /**
   * @param \App\Models\Service $service
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(Service $service): JsonResponse
  {
    return $this->success($service);
  }

  /**
   * @param \App\Http\Requests\UpdateServiceRequest $request
   * @param \App\Models\Service $service
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(UpdateServiceRequest $request, Service $service): JsonResponse
  {
    $service->update($request->validated());

    return $this->success($service, "Service updated successfully");
  }

  /**
   * @param \App\Models\Service $service
   * @return \Illuminate\Http\JsonResponse
   */
  public function destroy(Service $service): JsonResponse
  {
    $service->delete();

    return $this->success(null, "Service deleted successfully");
  }
}
