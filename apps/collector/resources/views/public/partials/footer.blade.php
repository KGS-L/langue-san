<footer class="san-footer py-5">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <a class="san-brand mb-3" href="{{ route('home') }}">
                    <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="38" height="38" alt="Logo Langue SAN">
                    <span>Langue SAN</span>
                </a>
                <p class="mb-2">Un projet ouvert pour documenter, valider et transmettre le San dans le numérique.</p>
                <small>Collecter · Valider · Transmettre</small>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="h6 fw-bold text-dark">Projet</h2>
                <div class="d-flex flex-column gap-2 small">
                    <a href="{{ route('home') }}#pourquoi">À propos</a>
                    <a href="{{ route('home') }}#fonctionnement">Comment contribuer</a>
                    <a href="{{ route('home') }}#themes">Thèmes</a>
                    <a href="{{ route('contributor.home') }}">Contribuer</a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="h6 fw-bold text-dark">Données & règles</h2>
                <div class="d-flex flex-column gap-2 small">
                    <a href="{{ route('privacy') }}">Confidentialité</a>
                    <a href="{{ route('contribution-policy') }}">Politique de contribution</a>
                    <a href="{{ route('data-governance') }}">Gouvernance des données</a>
                </div>
            </div>

            <div class="col-lg-3">
                <h2 class="h6 fw-bold text-dark">Open source</h2>
                <div class="d-flex flex-column gap-2 small">
                    <a href="https://github.com/KGS-L/langue-san" target="_blank" rel="noopener noreferrer"><i class="bi bi-github me-1"></i>Dépôt GitHub</a>
                    <a href="https://github.com/KGS-L/langue-san/blob/main/CODE_OF_CONDUCT.md" target="_blank" rel="noopener noreferrer">Code de conduite</a>
                    <a href="https://github.com/KGS-L/langue-san/blob/main/CONTRIBUTING.md" target="_blank" rel="noopener noreferrer">Contribuer au code / projet</a>
                    <a href="https://github.com/KGS-L/langue-san/blob/main/LICENSE" target="_blank" rel="noopener noreferrer">Licence du code</a>
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
