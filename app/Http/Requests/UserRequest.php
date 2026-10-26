<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin') ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user'); // null saat create

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
            // Password wajib saat membuat user; saat edit boleh dikosongkan (tidak diubah).
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
