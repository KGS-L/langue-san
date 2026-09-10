<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
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
    public function index(){ $this->authorize('viewAny', User::class); return view('admin.users.index',['users'=>$this->service->paginate()]); }
    public function create(){ $this->authorize('create',User::class); return view('admin.users.create',['roles'=>UserRole::cases(),'statuses'=>UserStatus::cases()]); }
    public function store(StoreUserRequest $request): RedirectResponse { $this->service->create($request->validated()); return redirect()->route('admin.users.index')->with('success','Utilisateur créé.'); }
    public function edit(User $user){ $this->authorize('update',$user); return view('admin.users.edit',['user'=>$user,'roles'=>UserRole::cases(),'statuses'=>UserStatus::cases()]); }
    public function update(UpdateUserRequest $request, User $user): RedirectResponse { $this->service->update($user,$request->validated()); return redirect()->route('admin.users.index')->with('success','Utilisateur mis à jour.'); }
    public function destroy(User $user): RedirectResponse { $this->authorize('delete',$user); $this->service->delete($user); return back()->with('success','Utilisateur supprimé.'); }
}
