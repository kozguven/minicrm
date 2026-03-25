<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $contact = $this->route('contact');

        if (! $contact instanceof Contact) {
            return false;
        }

        return $this->user()?->can('update', $contact) ?? false;
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
}
