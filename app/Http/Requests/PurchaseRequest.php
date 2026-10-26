<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-purchases') ?? false;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required', 'integer',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:999999999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'supplier_id' => 'pemasok',
            'items' => 'item pembelian',
            'items.*.product_id' => 'produk',
            'items.*.quantity' => 'jumlah',
            'items.*.unit_cost' => 'harga beli',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Tambahkan minimal satu item pembelian.',
            'items.min' => 'Tambahkan minimal satu item pembelian.',
        ];
    }
}
