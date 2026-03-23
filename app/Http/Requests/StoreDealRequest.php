<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Deal::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'opportunity_id' => [
                'required',
                'integer',
                Rule::exists('opportunities', 'id'),
                Rule::unique('deals', 'opportunity_id'),
            ],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'closed_at' => ['nullable', 'date'],
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
            'opportunity_id.unique' => 'Bu firsat zaten bir anlasmaya donusturuldu.',
            'amount.numeric' => 'Anlasma tutari sayi olmalidir.',
            'amount.min' => 'Anlasma tutari sifirdan kucuk olamaz.',
            'closed_at.date' => 'Kapanis tarihi gecerli bir tarih olmalidir.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'opportunity_id' => 'Firsat',
            'amount' => 'Anlasma tutari',
            'closed_at' => 'Kapanis tarihi',
        ];
    }
}
