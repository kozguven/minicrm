<?php

namespace App\Http\Requests;

use App\Models\Opportunity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $opportunity = $this->route('opportunity');

        if (! $opportunity instanceof Opportunity) {
            return false;
        }

        return $this->user()?->can('update', $opportunity) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'integer', Rule::exists('contacts', 'id')],
            'opportunity_stage_id' => ['required', 'integer', Rule::exists('opportunity_stages', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
        ];
    }
}
