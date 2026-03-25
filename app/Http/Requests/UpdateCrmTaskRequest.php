<?php

namespace App\Http\Requests;

use App\Models\CrmTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('crmTask');

        if (! $task instanceof CrmTask) {
            return false;
        }

        return $this->user()?->can('update', $task) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'opportunity_id' => ['required', 'integer', Rule::exists('opportunities', 'id')],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high'])],
            'task_type' => ['nullable', 'string', Rule::in(['manual', 'sla_follow_up', 'stage_follow_up'])],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
