<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Contact::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'owner_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'lead_source' => ['nullable', 'string', 'max:64'],
            'lead_status' => ['nullable', 'string', Rule::in(['new', 'qualified', 'contacted', 'lost'])],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high'])],
            'last_contacted_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'Lutfen bir sirket secin.',
            'company_id.integer' => 'Sirket secimi sayi olmalidir.',
            'company_id.exists' => 'Lutfen gecerli bir sirket secin.',
            'owner_user_id.exists' => 'Lutfen gecerli bir sorumlu secin.',
            'required' => ':attribute alani zorunludur.',
            'string' => ':attribute metin olmalidir.',
            'max.string' => ':attribute en fazla :max karakter olabilir.',
            'email' => ':attribute gecerli bir e-posta adresi olmalidir.',
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
            'company_id' => 'Sirket',
            'owner_user_id' => 'Sorumlu',
            'first_name' => 'Ad',
            'last_name' => 'Soyad',
            'email' => 'E-posta',
            'phone' => 'Telefon',
            'lead_source' => 'Lead kaynagi',
            'lead_status' => 'Lead durumu',
            'priority' => 'Oncelik',
            'last_contacted_at' => 'Son temas tarihi',
        ];
    }
}
