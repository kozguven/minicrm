<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;
use App\Services\Permissions\PermissionResolver;

class DealPolicy
{
    public function __construct(
        private readonly PermissionResolver $permissionResolver,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissionResolver->can($user, 'companies.view');
    }

    public function view(User $user, Deal $deal): bool
    {
        return $this->permissionResolver->can($user, 'companies.view');
    }

    public function create(User $user): bool
    {
        return $this->permissionResolver->can($user, 'companies.view')
            && $this->permissionResolver->can($user, 'companies.create');
    }
}
