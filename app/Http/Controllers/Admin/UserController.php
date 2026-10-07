<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        return view('admin.users.index', [
            'users' => User::query()->with('roles')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        return view('admin.users.form', [
            'account' => new User,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->roles()->sync([$data['role_id']]);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        return view('admin.users.form', [
            'account' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::query()->findOrFail($data['role_id']);

        if ($user->hasRole('admin') && $role->slug !== 'admin') {
            $admins = User::query()->whereHas('roles', fn ($query) => $query->where('slug', 'admin'))->count();
            if ($admins <= 1) {
                throw ValidationException::withMessages([
                    'role_id' => 'The last administrator cannot be reassigned.',
                ]);
            }
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->roles()->sync([$role->id]);

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function assignRoles(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('users.manage'), 403);

        $data = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $assignments = collect($data['roles'])->mapWithKeys(
            fn ($roleId, $userId) => [(int) $userId => (int) $roleId],
        );

        $knownUsers = User::query()->whereIn('id', $assignments->keys())->pluck('id');
        if ($knownUsers->count() !== $assignments->count()) {
            throw ValidationException::withMessages([
                'roles' => 'Choose a role for each person on the desk.',
            ]);
        }

        $adminRoleId = (int) Role::query()->where('slug', 'admin')->value('id');
        $untouchedAdmins = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'admin'))
            ->whereNotIn('id', $assignments->keys())
            ->count();
        $submittedAdmins = $assignments->filter(fn (int $roleId) => $roleId === $adminRoleId)->count();

        if ($untouchedAdmins + $submittedAdmins < 1) {
            throw ValidationException::withMessages([
                'roles' => 'Keep at least one administrator.',
            ]);
        }

        foreach ($assignments as $userId => $roleId) {
            User::query()->find($userId)?->roles()->sync([$roleId]);
        }

        return back()->with('status', 'Roles updated.');
    }
}
