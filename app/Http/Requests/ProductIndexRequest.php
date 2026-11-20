<?php

namespace App\Http\Requests;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductIndexRequest extends FormRequest
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
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', Rule::in([Product::STATUS_AKTIF, Product::STATUS_NONAKTIF, Product::STATUS_HABIS])],
            'sort' => ['nullable', Rule::in(['newest', 'name_asc', 'price_asc', 'price_desc'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.max' => 'Kata kunci maksimal 100 karakter.',
            'category.integer' => 'Kategori tidak valid.',
            'category.exists' => 'Kategori yang dipilih tidak tersedia.',
            'status.in' => 'Filter status tidak valid.',
            'sort.in' => 'Pilihan urutan tidak valid.',
        ];
    }

    /**
     * @return array{q: string|null, category: int|null, status: string|null, sort: string}
     */
    public function filters(): array
    {
        return [
            'q' => $this->input('q'),
            'category' => $this->integer('category') ?: null,
            'status' => $this->input('status'),
            'sort' => $this->input('sort', 'newest'),
        ];
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->input('q'))
            || filled($this->input('category'))
            || filled($this->input('status'));
    }

    protected function failedValidation(Validator $validator): void
    {
        app(ActivityLogService::class)->record(
            ActivityLog::ACTION_SEARCH_ERROR,
            $this->user(),
            null,
            null,
            ['filters' => $this->query(), 'errors' => $validator->errors()->all()],
            'Pencarian produk gagal karena filter tidak valid.',
        );

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
