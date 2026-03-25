<?php

namespace App\Services\Validation;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Support\Str;

class DuplicateRecordService
{
    /**
     * @return list<string>
     */
    public function companyWarnings(string $name, ?string $website = null, ?int $ignoreCompanyId = null): array
    {
        $warnings = [];

        $normalizedName = Str::lower(trim($name));
        if ($normalizedName !== '') {
            $nameExists = Company::query()
                ->when($ignoreCompanyId !== null, fn ($query) => $query->where('id', '!=', $ignoreCompanyId))
                ->get(['id', 'name'])
                ->contains(fn (Company $company): bool => Str::lower(trim((string) $company->name)) === $normalizedName);

            if ($nameExists) {
                $warnings[] = 'Benzer sirket ismine sahip bir kayit zaten var.';
            }
        }

        $normalizedWebsite = $this->normalizeWebsite($website);
        if ($normalizedWebsite !== null) {
            $websiteExists = Company::query()
                ->when($ignoreCompanyId !== null, fn ($query) => $query->where('id', '!=', $ignoreCompanyId))
                ->whereNotNull('website')
                ->get(['id', 'website'])
                ->contains(fn (Company $company): bool => $this->normalizeWebsite($company->website) === $normalizedWebsite);

            if ($websiteExists) {
                $warnings[] = 'Ayni web sitesiyle kayitli bir sirket zaten var.';
            }
        }

        return $warnings;
    }

    /**
     * @return list<string>
     */
    public function contactWarnings(?string $email = null, ?string $phone = null, ?int $ignoreContactId = null): array
    {
        $warnings = [];

        $normalizedEmail = $this->normalizeEmail($email);
        if ($normalizedEmail !== null) {
            $emailExists = Contact::query()
                ->when($ignoreContactId !== null, fn ($query) => $query->where('id', '!=', $ignoreContactId))
                ->whereNotNull('email')
                ->get(['id', 'email'])
                ->contains(fn (Contact $contact): bool => $this->normalizeEmail($contact->email) === $normalizedEmail);

            if ($emailExists) {
                $warnings[] = 'Ayni e-posta adresiyle kayitli bir kisi zaten var.';
            }
        }

        $normalizedPhone = $this->normalizePhone($phone);
        if ($normalizedPhone !== null) {
            $phoneExists = Contact::query()
                ->when($ignoreContactId !== null, fn ($query) => $query->where('id', '!=', $ignoreContactId))
                ->whereNotNull('phone')
                ->get(['id', 'phone'])
                ->contains(fn (Contact $contact): bool => $this->normalizePhone($contact->phone) === $normalizedPhone);

            if ($phoneExists) {
                $warnings[] = 'Ayni telefon numarasiyla kayitli bir kisi zaten var.';
            }
        }

        return $warnings;
    }

    private function normalizeEmail(?string $email): ?string
    {
        $value = trim((string) $email);

        return $value === '' ? null : Str::lower($value);
    }

    private function normalizePhone(?string $phone): ?string
    {
        $value = preg_replace('/\D+/', '', (string) $phone);

        return $value === null || $value === '' ? null : $value;
    }

    private function normalizeWebsite(?string $website): ?string
    {
        $value = trim((string) $website);

        return $value === '' ? null : Str::lower($value);
    }
}
