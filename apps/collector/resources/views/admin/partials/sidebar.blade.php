<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin') ? '' : 'collapsed' }}" href="{{ url('/admin') }}">
                <i class="bi bi-grid"></i><span>Dashboard</span>
            </a>
        </li>

        <li class="nav-heading">Collecte</li>

        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/categories*') ? '' : 'collapsed' }}" href="{{ url('/admin/categories') }}">
                <i class="bi bi-tags"></i><span>Catégories</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/prompts*') ? '' : 'collapsed' }}" href="{{ url('/admin/prompts') }}">
                <i class="bi bi-card-text"></i><span>Prompts</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/contributions*') ? '' : 'collapsed' }}" href="{{ url('/admin/contributions') }}">
                <i class="bi bi-mic"></i><span>Contributions</span>
            </a>
        </li>

        <li class="nav-heading">Validation</li>

        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/transcriptions*') ? '' : 'collapsed' }}" href="{{ url('/admin/transcriptions') }}">
                <i class="bi bi-headphones"></i><span>Transcriptions</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/validations*') ? '' : 'collapsed' }}" href="{{ url('/admin/validations') }}">
                <i class="bi bi-check2-circle"></i><span>Validations</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/localities*') ? '' : 'collapsed' }}" href="{{ url('/admin/localities') }}">
                <i class="bi bi-geo-alt"></i><span>Localités & variétés</span>
            </a>
        </li>

        <li class="nav-heading">Données</li>

        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/exports*') ? '' : 'collapsed' }}" href="{{ url('/admin/exports') }}">
                <i class="bi bi-download"></i><span>Exports dataset</span>
            </a>
        </li>
    </ul>
</aside>
