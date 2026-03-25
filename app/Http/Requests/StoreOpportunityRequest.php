<?php

namespace App\Http\Requests;

use App\Models\Opportunity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Opportunity::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'integer', Rule::exists('contacts', 'id')],
            'owner_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'opportunity_stage_id' => ['required', 'integer', Rule::exists('opportunity_stages', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'next_step' => ['nullable', 'string', 'max:255'],
            'next_step_due_at' => ['nullable', 'date'],
            'health_status' => ['nullable', 'string', Rule::in(['commit', 'watch', 'risk'])],
        ];
    }
}
