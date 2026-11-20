<?php

namespace App\Http\Requests;

use App\Models\ActivityLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityLogIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['nullable', Rule::in(array_keys(ActivityLog::actionLabels()))],
            'product' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.in' => 'Jenis aktivitas tidak valid.',
            'product.max' => 'Nama atau SKU produk maksimal 100 karakter.',
            'date_from.date' => 'Tanggal mulai tidak valid.',
            'date_to.date' => 'Tanggal selesai tidak valid.',
            'date_to.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }

    /**
     * @return array{action: string|null, product: string|null, date_from: string|null, date_to: string|null}
     */
    public function filters(): array
    {
        return [
            'action' => $this->input('action'),
            'product' => $this->input('product'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
        ];
    }

    public function hasActiveFilters(): bool
    {
        return collect($this->filters())->contains(fn (mixed $value): bool => filled($value));
    }
}
