<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Services\Permissions\PermissionResolver;

class CompanyPolicy
{
    public function __construct(
        private readonly PermissionResolver $permissionResolver,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissionResolver->can($user, 'companies.view');
    }

    public function view(User $user, Company $company): bool
    {
        return $this->permissionResolver->can($user, 'companies.view');
    }

    public function create(User $user): bool
    {
        return $this->permissionResolver->can($user, 'companies.create');
    }
}
