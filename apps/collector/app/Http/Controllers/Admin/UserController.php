<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    public function index()
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => $this->service->paginate(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->service->inviteModerator($request->validated());

        return redirect()->route('admin.users.index')
            ->with('success', 'Le modérateur a été invité par email.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        abort_unless($user->isModerator(), 404);

        return view('admin.users.edit', [
            'user' => $user,
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->service->updateModerator($user, $request->validated());

        return redirect()->route('admin.users.index')
            ->with('success', 'Modérateur mis à jour.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        abort_unless($user->isModerator(), 404);
        $this->service->delete($user);

        return back()->with('success', 'Modérateur supprimé.');
    }
}
