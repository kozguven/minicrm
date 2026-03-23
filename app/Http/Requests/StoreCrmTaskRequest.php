<?php

namespace App\Http\Requests;

use App\Models\CrmTask;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrmTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CrmTask::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'opportunity_id' => ['required', 'integer', Rule::exists('opportunities', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
