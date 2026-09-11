@php
    $currentUser = auth()->user();
    $currentRoleLabel = 'Membre de l’équipe';

    if ($currentUser) {
        foreach ([
            \App\Enums\UserRole::ADMIN,
            \App\Enums\UserRole::MODERATOR,
            \App\Enums\UserRole::VALIDATOR,
            \App\Enums\UserRole::TRANSCRIBER,
            \App\Enums\UserRole::CONTRIBUTOR,
        ] as $role) {
            if ($currentUser->hasRole($role->value)) {
                $currentRoleLabel = $role->label();
                break;
            }
        }
    }
@endphp

<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.dashboard') }}" class="logo d-flex align-items-center">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" alt="Logo Langue SAN">
            <span class="d-none d-lg-block">Langue SAN</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4"></i>
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{ $currentUser?->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{ $currentUser?->name }}</h6>
                        <span>{{ $currentRoleLabel }}</span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @if($currentUser?->isContributor())
                        <li><a class="dropdown-item d-flex align-items-center" href="{{ route('contributor.dashboard') }}"><i class="bi bi-person-workspace"></i><span>Mon espace contributeur</span></a></li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item d-flex align-items-center" type="submit">
                                <i class="bi bi-box-arrow-right"></i><span>Déconnexion</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</header>
