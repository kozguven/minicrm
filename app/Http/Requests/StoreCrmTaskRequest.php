<?php

namespace App\Http\Requests;

use App\Models\CrmTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrmTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CrmTask::class) ?? false;
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'opportunity_id.required' => 'Lutfen bir firsat secin.',
            'opportunity_id.integer' => 'Firsat secimi sayi olmalidir.',
            'opportunity_id.exists' => 'Lutfen gecerli bir firsat secin.',
            'assigned_user_id.exists' => 'Lutfen gecerli bir atanan secin.',
            'required' => ':attribute alani zorunludur.',
            'string' => ':attribute metin olmalidir.',
            'max.string' => ':attribute en fazla :max karakter olabilir.',
            'date' => ':attribute gecerli bir tarih olmalidir.',
            'in' => ':attribute degeri gecersiz.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'opportunity_id' => 'Firsat',
            'assigned_user_id' => 'Atanan kullanici',
            'title' => 'Gorev basligi',
            'priority' => 'Oncelik',
            'task_type' => 'Gorev tipi',
            'due_at' => 'Termin',
        ];
    }
}
