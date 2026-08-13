<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
  use AuthorizesRequests, ValidatesRequests;

  /**
   * @param mixed $data
   * @param string $message
   * @param int $status
   * @return \Illuminate\Http\JsonResponse
   */
  protected function success($data = null, string $message = "Success", int $status = 200): JsonResponse
  {
    return response()->json(
      [
        "success" => true,
        "message" => $message,
        "data" => $data,
      ],
      $status,
    );
  }

  /**
   * @param string $message
   * @param int $status
   * @param mixed $errors
   * @return \Illuminate\Http\JsonResponse
   */
  protected function error(string $message = "Error", int $status = 400, $errors = null): JsonResponse
  {
    return response()->json(
      [
        "success" => false,
        "message" => $message,
        "errors" => $errors,
      ],
      $status,
    );
  }
}
