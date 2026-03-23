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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'opportunity_stage_id.required' => 'Lutfen bir asama secin.',
            'opportunity_stage_id.integer' => 'Asama secimi sayi olmalidir.',
            'opportunity_stage_id.exists' => 'Lutfen gecerli bir asama secin.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'opportunity_stage_id' => 'Asama',
        ];
    }
}
