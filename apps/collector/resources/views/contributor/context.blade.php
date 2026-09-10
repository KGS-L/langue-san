<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Votre contexte - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="34" height="34" alt="Logo Langue SAN">
            Langue SAN
        </a>
        <a href="{{ route('contributor.home') }}" class="btn btn-outline-secondary btn-sm">Retour</a>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Étape 1 sur 3</span>
                    <span class="badge bg-light text-dark border">{{ $profile->public_code }}</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" style="width: 33%" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-2">Votre contexte linguistique</h1>
                    <p class="text-muted">Ces informations servent à interpréter correctement vos réponses. Nous ne vous demandons pas de choisir vous-même un nom technique de variété.</p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contributor.context.update') }}" class="row g-4">
                        @csrf

                        <div class="col-12">
                            <label class="form-label fw-semibold">Dans quelle ville ou localité avez-vous principalement appris ou parlé le San ?</label>
                            <select name="locality_id" class="form-select">
                                <option value="">Autre localité / je préfère préciser</option>
                                @foreach($localities as $locality)
                                    <option value="{{ $locality->id }}" @selected(old('locality_id', $profile->locality_id) == $locality->id)>
                                        {{ $locality->name }}{{ $locality->province ? ' — '.$locality->province : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">La localité aide les validateurs à ne pas mélanger des usages différents.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Si votre localité n’est pas dans la liste</label>
                            <input type="text" name="locality_other" class="form-control" maxlength="150" value="{{ old('locality_other', $profile->locality_other) }}" placeholder="Ex. nom du village ou de la ville">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Comment évaluez-vous votre pratique du San ?</label>
                            <select name="fluency_level" class="form-select" required>
                                <option value="">Choisir</option>
                                <option value="native" @selected(old('fluency_level', $profile->fluency_level) === 'native')>Langue maternelle / je le parle depuis l’enfance</option>
                                <option value="fluent" @selected(old('fluency_level', $profile->fluency_level) === 'fluent')>Je le parle couramment</option>
                                <option value="intermediate" @selected(old('fluency_level', $profile->fluency_level) === 'intermediate')>Je le parle assez bien</option>
                                <option value="basic" @selected(old('fluency_level', $profile->fluency_level) === 'basic')>J’en connais quelques mots ou expressions</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold d-block">Savez-vous écrire le San ?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="can_write_san" id="write_yes" value="1" @checked((string) old('can_write_san', $profile->can_write_san === null ? '' : (int) $profile->can_write_san) === '1') required>
                                <label class="form-check-label" for="write_yes">Oui</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="can_write_san" id="write_no" value="0" @checked((string) old('can_write_san', $profile->can_write_san === null ? '' : (int) $profile->can_write_san) === '0') required>
                                <label class="form-check-label" for="write_no">Non / pas vraiment</label>
                            </div>
                            <div class="form-text">Même si vous ne savez pas l’écrire, vous pourrez répondre par audio.</div>
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row gap-2">
                            <button class="btn btn-primary btn-lg" type="submit">
                                Continuer <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                            <a href="{{ route('contributor.home') }}" class="btn btn-light btn-lg">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
