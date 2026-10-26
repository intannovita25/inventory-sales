<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-sales') ?? false;
    }

    /**
     * Perhatikan: harga & total TIDAK divalidasi/dipakai dari input.
     * Server mengambil harga dari database.
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required', 'integer',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'items' => 'item penjualan',
            'items.*.product_id' => 'produk',
            'items.*.quantity' => 'jumlah',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Tambahkan minimal satu item penjualan.',
            'items.min' => 'Tambahkan minimal satu item penjualan.',
        ];
    }
}
