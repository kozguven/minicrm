<?php

namespace App\Http\Requests;

use App\Models\Deal;
use App\Models\Opportunity;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ConvertOpportunityToDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Deal::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $opportunity = $this->route('opportunity');

                if ($opportunity instanceof Opportunity && $opportunity->deal()->exists()) {
                    $validator->errors()->add('opportunity_id', 'Bu firsat zaten bir anlasmaya donusturuldu.');
                }
            },
        ];
    }
}
