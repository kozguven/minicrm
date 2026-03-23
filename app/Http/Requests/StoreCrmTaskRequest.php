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
            'title' => ['required', 'string', 'max:255'],
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
            'required' => ':attribute alani zorunludur.',
            'string' => ':attribute metin olmalidir.',
            'max.string' => ':attribute en fazla :max karakter olabilir.',
            'date' => ':attribute gecerli bir tarih olmalidir.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'opportunity_id' => 'Firsat',
            'title' => 'Gorev basligi',
            'due_at' => 'Termin',
        ];
    }
}
