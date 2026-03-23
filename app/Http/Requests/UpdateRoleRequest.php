<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        /** @var Role $role */
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role),
                function (string $attribute, mixed $value, \Closure $fail) use ($role): void {
                    if ($role->name === 'Admin' && $value !== 'Admin') {
                        $fail('Admin role adi degistirilemez.');
                    }
                },
            ],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'key')],
        ];
    }
}
