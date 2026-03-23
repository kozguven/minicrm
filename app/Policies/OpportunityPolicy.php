<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;
use App\Services\Permissions\PermissionResolver;

class OpportunityPolicy
{
    public function __construct(
        private readonly PermissionResolver $permissionResolver,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissionResolver->can($user, 'companies.view');
    }

    public function create(User $user): bool
    {
        return $this->permissionResolver->can($user, 'companies.create');
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $this->permissionResolver->can($user, 'opportunities.edit');
    }
}
