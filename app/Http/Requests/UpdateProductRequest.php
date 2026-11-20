<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:150', Rule::unique('products', 'name')->ignore($product->id)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'status' => ['required', Rule::in([Product::STATUS_AKTIF, Product::STATUS_NONAKTIF])],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('description') && blank($this->input('description'))) {
            $this->merge(['description' => null]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new StoreProductRequest)->messages();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return (new StoreProductRequest)->attributes();
    }
}
