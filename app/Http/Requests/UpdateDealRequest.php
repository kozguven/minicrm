<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        $deal = $this->route('deal');

        if (! $deal instanceof Deal) {
            return false;
        }

        return $this->user()?->can('update', $deal) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['nullable', 'numeric', 'min:0'],
            'closed_at' => ['nullable', 'date'],
        ];
    }
}
