<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamMemberRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('team.index', [
            'teamMembers' => User::query()
                ->with(['roles' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('team.create', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTeamMemberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $teamMember = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $roleIds = Role::query()
            ->whereIn('name', $validated['roles'])
            ->pluck('id');

        $teamMember->roles()->sync($roleIds);

        return redirect('/team');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin(),
            403,
        );
    }
}
