<?php

namespace App\Http\Requests;

use App\Models\ContactInteraction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ContactInteraction::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'integer', Rule::exists('contacts', 'id')],
            'channel' => ['required', 'string', Rule::in(['call', 'meeting', 'email', 'whatsapp', 'other'])],
            'happened_at' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'follow_up_due_at' => ['nullable', 'date', 'after_or_equal:happened_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_id.required' => 'Lutfen bir kisi secin.',
            'contact_id.integer' => 'Kisi secimi sayi olmalidir.',
            'contact_id.exists' => 'Lutfen gecerli bir kisi secin.',
            'channel.required' => 'Lutfen gorusme kanalini secin.',
            'channel.in' => 'Gorusme kanali gecersiz.',
            'required' => ':attribute alani zorunludur.',
            'string' => ':attribute metin olmalidir.',
            'max.string' => ':attribute en fazla :max karakter olabilir.',
            'date' => ':attribute gecerli bir tarih olmalidir.',
            'after_or_equal' => ':attribute gorusme tarihinden once olamaz.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'happened_at' => 'Gorusme tarihi',
            'summary' => 'Gorusme ozeti',
            'notes' => 'Detay notu',
            'follow_up_due_at' => 'Takip tarihi',
        ];
    }
}
