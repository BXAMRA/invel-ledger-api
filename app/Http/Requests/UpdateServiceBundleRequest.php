<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceBundleRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  /**
     * Get the validation rules that apply to the request.

     *

     * @return array<string, array<int, string>>
     */
  public function rules(): array
  {
    return [
      "name" => ["sometimes", "required", "string", "max:255"],
      "description" => ["nullable", "string"],
      "services" => ["nullable", "array"],
      "services.*" => ["integer", "exists:services,id"],
    ];
  }
}
