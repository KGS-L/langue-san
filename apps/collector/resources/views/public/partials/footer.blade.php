<footer class="san-footer py-5">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <a class="san-brand mb-3" href="{{ route('home') }}">
                    <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="38" height="38" alt="Logo Langue SAN">
                    <span>Langue SAN</span>
                </a>
                <p class="mb-2">Un projet ouvert pour préserver les voix, documenter le San et préparer de futurs outils de traduction et d’apprentissage.</p>
                <small>Collecter · Valider · Transmettre</small>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="h6 fw-bold text-dark">Communauté</h2>
                <div class="d-flex flex-column gap-2 small">
                    <a href="{{ route('contributor.home') }}">Contribuer</a>
                    <a href="{{ route('community') }}">La communauté</a>
                    <a href="{{ route('project.join') }}">Rejoindre le projet</a>
                    @guest<a href="{{ route('contributor.auth.show') }}">Mon compte</a>@endguest
                    @auth @if(auth()->user()->isContributor())<a href="{{ route('contributor.dashboard') }}">Mon espace</a>@endif @endauth
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="h6 fw-bold text-dark">Projet</h2>
                <div class="d-flex flex-column gap-2 small">
                    <a href="{{ route('home') }}#pourquoi">À propos</a>
                    <a href="{{ route('home') }}#fonctionnement">Comment contribuer</a>
                    <a href="https://github.com/KGS-L/langue-san" target="_blank" rel="noopener noreferrer"><i class="bi bi-github me-1"></i>Dépôt GitHub</a>
                    <a href="https://github.com/KGS-L/langue-san/blob/main/CONTRIBUTING.md" target="_blank" rel="noopener noreferrer">Guide de contribution</a>
                    <a href="https://github.com/KGS-L/langue-san/blob/main/LICENSE" target="_blank" rel="noopener noreferrer">Licence du code</a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="h6 fw-bold text-dark">Données & règles</h2>
                <div class="d-flex flex-column gap-2 small">
                    <a href="{{ route('privacy') }}">Confidentialité</a>
                    <a href="{{ route('contribution-policy') }}">Politique de contribution</a>
                    <a href="{{ route('data-governance') }}">Gouvernance des données</a>
                    <a href="https://github.com/KGS-L/langue-san/blob/main/CODE_OF_CONDUCT.md" target="_blank" rel="noopener noreferrer">Code de conduite</a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="h6 fw-bold text-dark">Équipe</h2>
                <div class="d-flex flex-column gap-2 small">
                    <a href="{{ route('login') }}"><i class="bi bi-shield-lock me-1"></i>Accès équipe</a>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 small">
            <span>© {{ date('Y') }} Langue SAN.</span>
            <span>Projet open source — les licences des données et des audios sont gérées séparément de celle du code.</span>
        </div>
    </div>
</footer>
