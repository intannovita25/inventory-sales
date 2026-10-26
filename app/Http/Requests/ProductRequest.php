<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin') ?? false;
    }

    public function rules(): array
    {
        $product = $this->route('product'); // null saat create

        return [
            'sku' => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            // Stok awal hanya diisi saat membuat produk. Setelahnya stok berubah lewat transaksi.
            'stock' => $this->isMethod('post')
                ? ['required', 'integer', 'min:0']
                : ['sometimes', 'integer', 'min:0'],
        ];
    }
}
