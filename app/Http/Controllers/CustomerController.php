<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    // Allow optional search and pagination
    $query = Customer::query();

    if ($request->has("search")) {
      $search = $request->query("search");
      $query
        ->where("company_name", "like", "%{$search}%")
        ->orWhere("email", "like", "%{$search}%")
        ->orWhere("phone", "like", "%{$search}%");
    }

    $customers = $query->latest()->paginate($request->query("per_page", 15));

    return $this->success($customers);
  }

  /**
   * @param \App\Http\Requests\StoreCustomerRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(StoreCustomerRequest $request): JsonResponse
  {
    $customer = Customer::query()->create($request->validated());

    return $this->success($customer, "Customer created successfully", 201);
  }

  /**
   * @param \App\Models\Customer $customer
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(Customer $customer): JsonResponse
  {
    $customer->load("documents");

    return $this->success($customer);
  }

  /**
   * @param \App\Http\Requests\UpdateCustomerRequest $request
   * @param \App\Models\Customer $customer
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
  {
    $customer->update($request->validated());

    return $this->success($customer, "Customer updated successfully");
  }

  /**
   * @param \App\Models\Customer $customer
   * @return \Illuminate\Http\JsonResponse
   */
  public function destroy(Customer $customer): JsonResponse
  {
    $customer->delete();

    return $this->success(null, "Customer deleted successfully");
  }
}
