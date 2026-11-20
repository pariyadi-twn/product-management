<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

class DuplicateProductException extends RuntimeException
{
    public function __construct(
        public readonly string $field,
        public readonly Product $product,
    ) {
        parent::__construct('Data produk sudah digunakan.');
    }

    public function userMessage(): string
    {
        return match ($this->field) {
            'sku' => 'SKU "'.$this->product->sku.'" sudah digunakan produk lain.',
            default => 'Nama produk "'.$this->product->name.'" sudah digunakan produk lain.',
        };
    }
}
