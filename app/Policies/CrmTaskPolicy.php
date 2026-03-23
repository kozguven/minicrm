<?php

namespace App\Policies;

use App\Models\CrmTask;
use App\Models\User;
use App\Services\Permissions\PermissionResolver;

class CrmTaskPolicy
{
    public function __construct(
        private readonly PermissionResolver $permissionResolver,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissionResolver->can($user, 'companies.view');
    }

    public function view(User $user, CrmTask $crmTask): bool
    {
        return $this->permissionResolver->can($user, 'companies.view');
    }

    public function create(User $user): bool
    {
        return $this->permissionResolver->can($user, 'companies.view')
            && $this->permissionResolver->can($user, 'companies.create');
    }
}
