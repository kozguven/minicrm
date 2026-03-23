<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('roles.index', [
            'roles' => Role::query()->with('permissions')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('roles.create', [
            'permissions' => Permission::query()->orderBy('key')->get(),
        ]);
    }

    public function store(StoreRoleRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validated();
        $role = Role::query()->create([
            'name' => $validated['name'],
        ]);

        $this->syncPermissions(
            role: $role,
            permissionKeys: $validated['permissions'] ?? [],
            userId: $request->user()?->id,
            auditLogger: $auditLogger,
        );

        return redirect('/roles');
    }

    public function edit(Request $request, Role $role): View
    {
        $this->ensureAdmin($request);

        return view('roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::query()->orderBy('key')->get(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'],
        ]);

        $this->syncPermissions(
            role: $role,
            permissionKeys: $validated['permissions'] ?? [],
            userId: $request->user()?->id,
            auditLogger: $auditLogger,
        );

        return redirect('/roles');
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    private function syncPermissions(
        Role $role,
        array $permissionKeys,
        ?int $userId,
        AuditLogger $auditLogger,
    ): void {
        $normalizedKeys = collect($permissionKeys)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $permissionIds = Permission::query()
            ->whereIn('key', $normalizedKeys)
            ->pluck('id');

        $role->permissions()->sync($permissionIds);

        $auditLogger->log(
            userId: $userId,
            entityType: Role::class,
            entityId: $role->id,
            action: 'permissions_synced',
            payload: ['permission_keys' => $normalizedKeys],
        );
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin(),
            403,
        );
    }
}
