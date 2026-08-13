<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceBundleRequest;
use App\Http\Requests\UpdateServiceBundleRequest;
use App\Models\ServiceBundle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceBundleController extends Controller
{
  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $query = ServiceBundle::with("services");

    if ($request->has("search")) {
      $search = $request->query("search");
      $query->where("name", "like", "%{$search}%");
    }

    $bundles = $query->latest()->paginate($request->query("per_page", 15));

    return $this->success($bundles);
  }

  /**
   * @param \App\Http\Requests\StoreServiceBundleRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(StoreServiceBundleRequest $request): JsonResponse
  {
    $bundle = ServiceBundle::query()->create($request->only(["name", "description"]));

    if ($request->has("services")) {
      $bundle->services()->sync($request->input("services"));
    }

    $bundle->load("services");

    return $this->success($bundle, "Service bundle created successfully", 201);
  }

  /**
   * @param \App\Models\ServiceBundle $bundle
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(ServiceBundle $bundle): JsonResponse
  {
    $bundle->load("services");

    return $this->success($bundle);
  }

  /**
   * @param \App\Http\Requests\UpdateServiceBundleRequest $request
   * @param \App\Models\ServiceBundle $bundle
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(UpdateServiceBundleRequest $request, ServiceBundle $bundle): JsonResponse
  {
    $bundle->update($request->only(["name", "description"]));

    if ($request->has("services")) {
      $bundle->services()->sync($request->input("services"));
    }

    $bundle->load("services");

    return $this->success($bundle, "Service bundle updated successfully");
  }

  /**
   * @param \App\Models\ServiceBundle $bundle
   * @return \Illuminate\Http\JsonResponse
   */
  public function destroy(ServiceBundle $bundle): JsonResponse
  {
    $bundle->delete();

    return $this->success(null, "Service bundle deleted successfully");
  }
}
