<?php

namespace App\Http\Requests;

use App\Models\Opportunity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOpportunityStageRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'opportunity_stage_id' => ['required', 'integer', Rule::exists('opportunity_stages', 'id')],
        ];
    }
}
