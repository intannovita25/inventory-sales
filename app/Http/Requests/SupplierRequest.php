<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => ['nullable', 'email', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
        ];
    }
}
