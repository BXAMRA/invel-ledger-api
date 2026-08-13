<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'pricing_type' => ['required', 'string', 'max:50'],
            'default_deliverables' => ['nullable', 'array'],
            'default_deliverables.*' => ['nullable', 'string'],
            'deliverables_heading' => ['nullable', 'string'],
        ];
    }
}
