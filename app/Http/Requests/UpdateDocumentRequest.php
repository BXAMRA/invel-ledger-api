<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
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
      "document_number" => ["nullable", "string", "max:255"],
      "customer_id" => ["sometimes", "required", "integer", "exists:customers,id"],
      "type" => ["sometimes", "required", "string", "in:invoice,quote,estimate"],
      "status" => ["nullable", "string", "in:draft,sent,paid,cancelled,partially_paid"],
      "issue_date" => ["sometimes", "required", "date"],
      "due_date" => ["nullable", "date"],
      "discount_flat" => ["nullable", "numeric", "min:0"],
      "discount_percentage" => ["nullable", "numeric", "min:0", "max:100"],
      "notes" => ["nullable", "string"],

      "items" => ["sometimes", "required", "array", "min:1"],
      "items.*.id" => ["nullable", "integer", "exists:document_items,id"],
      "items.*.service_id" => ["nullable", "integer", "exists:services,id"],
      "items.*.name" => ["required", "string", "max:255"],
      "items.*.description" => ["nullable", "string"],
      "items.*.deliverables" => ["nullable", "array"],
      "items.*.deliverables.*" => ["nullable", "string"],
      "items.*.deliverables_heading" => ["nullable", "string"],

      "items.*.unit_price" => ["required", "numeric", "min:0"],
      "items.*.discount_flat" => ["nullable", "numeric", "min:0"],
      "items.*.discount_percentage" => ["nullable", "numeric", "min:0", "max:100"],
      "items.*.tax_rate" => ["required", "numeric", "min:0", "max:100"],
      "items.*.pricing_type" => ["nullable", "string", "in:standard,included,complementary,provided_by_client"],
    ];
  }
}
