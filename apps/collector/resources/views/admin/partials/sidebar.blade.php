<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? '' : 'collapsed' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a></li>

        @if(auth()->user()->isStaff())
            <li class="nav-heading">Administration</li>
            @can('manage moderators')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.users.*') ? '' : 'collapsed' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i><span>Modérateurs</span></a></li>
            @endcan
            @can('review project applications')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.project-applications.*') ? '' : 'collapsed' }}" href="{{ route('admin.project-applications.index') }}"><i class="bi bi-person-plus"></i><span>Candidatures projet</span></a></li>
            @endcan
        @endif

        @if(auth()->user()->can('manage prompts') || auth()->user()->can('view contributions'))
            <li class="nav-heading">Collecte</li>
            @can('manage prompts')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.categories.*') ? '' : 'collapsed' }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i><span>Catégories</span></a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.prompts.*') ? '' : 'collapsed' }}" href="{{ route('admin.prompts.index') }}"><i class="bi bi-card-text"></i><span>Prompts</span></a></li>
            @endcan
            @can('view contributions')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.contributions.*') ? '' : 'collapsed' }}" href="{{ route('admin.contributions.index') }}"><i class="bi bi-mic"></i><span>Contributions</span></a></li>
            @endcan
            @can('transcribe contributions')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.transcriptions.*') ? '' : 'collapsed' }}" href="{{ route('admin.transcriptions.index') }}"><i class="bi bi-headphones"></i><span>À transcrire</span></a></li>
            @endcan
            @can('validate contributions')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.validations.*') ? '' : 'collapsed' }}" href="{{ route('admin.validations.index') }}"><i class="bi bi-patch-check"></i><span>À valider</span></a></li>
            @endcan
        @endif

        @can('manage reference data')
            <li class="nav-heading">Référentiel linguistique</li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.localities.*') ? '' : 'collapsed' }}" href="{{ route('admin.localities.index') }}"><i class="bi bi-geo-alt"></i><span>Localités</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.varieties.*') ? '' : 'collapsed' }}" href="{{ route('admin.varieties.index') }}"><i class="bi bi-translate"></i><span>Variétés</span></a></li>
        @endcan

        @can('export dataset')
            <li class="nav-heading">Données</li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.exports.*') ? '' : 'collapsed' }}" href="{{ route('admin.exports.index') }}"><i class="bi bi-download"></i><span>Exports dataset</span></a></li>
        @endcan
    </ul>
</aside>
