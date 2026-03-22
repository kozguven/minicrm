<?php

namespace App\Services\Permissions;

use App\Models\User;

class PermissionResolver
{
    public function can(User $user, string $permissionKey): bool
    {
        return $user->permissions()
            ->where('permissions.key', $permissionKey)
            ->exists();
    }
}
