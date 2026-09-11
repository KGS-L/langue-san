<section class="section-san section-soft">
    <div class="container">
        <div class="row align-items-end mb-4">
            <div class="col-lg-8">
                <div class="section-kicker">Une communauté ouverte</div>
                <h2 class="section-title display-6 mt-2">Le projet avance grâce à des personnes aux expériences différentes.</h2>
                <p class="section-copy mb-0">Locuteurs, enseignants, développeurs, chercheurs, spécialistes IA ou acteurs de terrain : chacun peut apporter une pièce utile au projet.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0"><a href="{{ route('community') }}" class="btn btn-san-outline">Découvrir la communauté <i class="bi bi-arrow-right ms-1"></i></a></div>
        </div>

        @if($communityProfiles->isNotEmpty())
            <div class="row g-3 mb-4">
                @foreach($communityProfiles as $profile)
                    <div class="col-sm-6 col-lg-3">
                        <div class="value-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="icon-box rounded-circle"><i class="bi bi-person"></i></div>
                                <div><div class="fw-bold">{{ $profile->public_display_name ?: $profile->user->name }}</div><div class="small text-muted">{{ $profile->professionLabel() }}</div></div>
                            </div>
                            <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $profile->country ?: 'Communauté Langue SAN' }}</div>
                            @if($profile->user->projectMembership?->is_active)<span class="badge mt-2" style="background:#f8f5ee;color:#8b671e">Membre du projet</span>@else<span class="badge text-bg-light border mt-2">Contributeur</span>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="cta-san d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 p-4">
            <div><h3 class="h4 fw-bold mb-1" style="color:#172640">Vous avez une compétence, une expérience ou un réseau à apporter ?</h3><p class="section-copy mb-0">Présentez-nous simplement ce que vous pouvez apporter. Aucun CV n’est obligatoire.</p></div>
            <a href="{{ route('project.join') }}" class="btn btn-san-primary flex-shrink-0">Rejoindre le projet</a>
        </div>
    </div>
</section>
