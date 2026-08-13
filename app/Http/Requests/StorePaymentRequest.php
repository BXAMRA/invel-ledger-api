<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
      "document_id" => ["required", "integer", "exists:documents,id"],
      "amount" => ["required", "numeric", "min:0.01"],
      "payment_date" => ["required", "date"],
      "payment_method" => ["required", "string", "max:100"],
      "reference_number" => ["nullable", "string", "max:255"],
      "notes" => ["nullable", "string"],
    ];
  }
}
