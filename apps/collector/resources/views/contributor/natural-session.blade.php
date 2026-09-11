<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parole naturelle - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
    <style>
        .record-button { width: 92px; height: 92px; border-radius: 50%; display: inline-grid; place-items: center; font-size: 2.1rem; }
        .recording-dot { width: 11px; height: 11px; border-radius: 50%; display: inline-block; background: #dc3545; animation: pulse 1s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.35} }
    </style>
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="34" height="34" alt="Logo Langue SAN"> Langue SAN
        </a>
        <span class="small text-muted">Parole naturelle</span>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div id="clientErrors" class="alert alert-danger d-none"></div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-light text-dark border">{{ $sessionPrompt->prompt->category->name }}</span>
                        <span class="badge text-bg-success">San → San</span>
                    </div>
                    <div class="small text-muted mb-2">Parlez librement en San à partir de ce sujet :</div>
                    <h1 class="h2 fw-bold mb-3">{{ $sessionPrompt->prompt->french_text }}</h1>
                    <p class="text-muted">Ne traduisez pas la consigne française mot à mot. Racontez naturellement avec vos propres mots, comme si vous parliez à une personne de votre entourage.</p>

                    <div class="alert alert-light border">
                        <strong>Durée recommandée : 2 à 5 minutes.</strong>
                        <div class="small text-muted">Vous pouvez parler plus brièvement si le sujet ne demande pas autant de temps. L’enregistrement s’arrêtera automatiquement au bout de 10 minutes.</div>
                    </div>

                    <form id="naturalSpeechForm" method="POST" enctype="multipart/form-data" action="{{ route('contributor.sessions.submit', [$session, $sessionPrompt]) }}">
                        @csrf
                        <input type="hidden" name="audio_duration_ms" id="audioDurationMs" value="">

                        <div class="border rounded-4 p-4 text-center bg-white">
                            <div id="recordingIdle">
                                <button id="startRecording" type="button" class="btn btn-primary record-button" aria-label="Commencer l’enregistrement"><i class="bi bi-mic-fill"></i></button>
                                <div class="fw-semibold mt-3">Commencer l’enregistrement</div>
                                <div class="small text-muted">Votre navigateur demandera l’accès au microphone.</div>
                            </div>

                            <div id="recordingActive" class="d-none">
                                <div class="mb-3"><span class="recording-dot me-2"></span><strong>Enregistrement en cours</strong></div>
                                <div id="recordingTimer" class="display-6 fw-bold mb-3">00:00</div>
                                <button id="stopRecording" type="button" class="btn btn-danger btn-lg rounded-pill px-4"><i class="bi bi-stop-fill me-1"></i>Arrêter</button>
                            </div>

                            <div id="recordingReady" class="d-none">
                                <div class="alert alert-success py-2"><i class="bi bi-check-circle me-1"></i>Enregistrement prêt.</div>
                                <audio id="audioPreview" class="w-100 mb-3" controls></audio>
                                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                    <button id="recordAgain" type="button" class="btn btn-outline-primary"><i class="bi bi-arrow-repeat me-1"></i>Recommencer</button>
                                    <button id="submitContribution" type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up me-1"></i>Envoyer ma contribution</button>
                                </div>
                            </div>

                            <div id="microphoneMessage" class="small text-muted mt-3"></div>
                        </div>
                    </form>

                    <a href="{{ route('contributor.natural-speech.index') }}" class="btn btn-link text-secondary w-100 mt-3">Choisir un autre sujet</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
(() => {
    const form = document.getElementById('naturalSpeechForm');
    const startButton = document.getElementById('startRecording');
    const stopButton = document.getElementById('stopRecording');
    const againButton = document.getElementById('recordAgain');
    const submitButton = document.getElementById('submitContribution');
    const idle = document.getElementById('recordingIdle');
    const active = document.getElementById('recordingActive');
    const ready = document.getElementById('recordingReady');
    const timer = document.getElementById('recordingTimer');
    const preview = document.getElementById('audioPreview');
    const message = document.getElementById('microphoneMessage');
    const errors = document.getElementById('clientErrors');
    const durationInput = document.getElementById('audioDurationMs');
    const maxDurationMs = 10 * 60 * 1000;

    let recorder = null;
    let stream = null;
    let chunks = [];
    let blob = null;
    let mimeType = '';
    let startedAt = 0;
    let durationMs = 0;
    let timerInterval = null;

    const formatTime = ms => {
        const seconds = Math.floor(ms / 1000);
        return `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    };

    const preferredMimeType = () => {
        if (!window.MediaRecorder) return '';
        return ['audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus', 'audio/mp4']
            .find(type => MediaRecorder.isTypeSupported(type)) || '';
    };

    const releaseStream = () => {
        if (stream) stream.getTracks().forEach(track => track.stop());
        stream = null;
    };

    const stopRecording = () => {
        if (recorder && recorder.state === 'recording') recorder.stop();
    };

    startButton.addEventListener('click', async () => {
        errors.classList.add('d-none');
        if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
            errors.textContent = 'Ce navigateur ne permet pas l’enregistrement vocal direct.';
            errors.classList.remove('d-none');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            chunks = [];
            blob = null;
            const preferred = preferredMimeType();
            recorder = preferred ? new MediaRecorder(stream, { mimeType: preferred }) : new MediaRecorder(stream);
            mimeType = recorder.mimeType || preferred || 'audio/webm';

            recorder.addEventListener('dataavailable', event => {
                if (event.data?.size) chunks.push(event.data);
            });

            recorder.addEventListener('stop', () => {
                durationMs = Math.max(0, Date.now() - startedAt);
                durationInput.value = String(durationMs);
                blob = new Blob(chunks, { type: mimeType });
                if (preview.src) URL.revokeObjectURL(preview.src);
                preview.src = URL.createObjectURL(blob);
                clearInterval(timerInterval);
                releaseStream();
                active.classList.add('d-none');
                ready.classList.remove('d-none');
                message.textContent = `Durée : ${formatTime(durationMs)}`;
            });

            recorder.start(500);
            startedAt = Date.now();
            timer.textContent = '00:00';
            idle.classList.add('d-none');
            ready.classList.add('d-none');
            active.classList.remove('d-none');
            message.textContent = 'Parlez naturellement en San. Prenez votre temps.';

            timerInterval = setInterval(() => {
                const elapsed = Date.now() - startedAt;
                timer.textContent = formatTime(elapsed);
                if (elapsed >= maxDurationMs) stopRecording();
            }, 500);
        } catch (error) {
            releaseStream();
            errors.textContent = 'Impossible d’utiliser le microphone. Vérifiez son autorisation dans le navigateur.';
            errors.classList.remove('d-none');
        }
    });

    stopButton.addEventListener('click', stopRecording);

    againButton.addEventListener('click', () => {
        if (preview.src) URL.revokeObjectURL(preview.src);
        preview.removeAttribute('src');
        preview.load();
        blob = null;
        chunks = [];
        durationMs = 0;
        durationInput.value = '';
        ready.classList.add('d-none');
        idle.classList.remove('d-none');
        message.textContent = '';
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        errors.classList.add('d-none');

        if (!blob) {
            errors.textContent = 'Enregistrez votre voix avant d’envoyer la contribution.';
            errors.classList.remove('d-none');
            return;
        }

        const data = new FormData(form);
        const extension = mimeType.includes('mp4') ? 'm4a' : (mimeType.includes('ogg') ? 'ogg' : 'webm');
        data.append('audio', new File([blob], `parole-naturelle-{{ $session->id }}-{{ $sessionPrompt->id }}.${extension}`, { type: mimeType }));
        data.set('audio_duration_ms', String(durationMs));

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi…';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: data,
                headers: { 'Accept': 'application/json, text/html', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (response.status === 422) {
                const payload = await response.json();
                errors.innerHTML = Object.values(payload.errors || {}).flat().map(value => `<div>${value}</div>`).join('');
                errors.classList.remove('d-none');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i>Envoyer ma contribution';
                return;
            }

            if (!response.ok) throw new Error('upload_failed');
            window.location.href = response.url || @json(route('contributor.sessions.show', $session));
        } catch (error) {
            errors.textContent = 'L’enregistrement n’a pas pu être envoyé. Vérifiez votre connexion puis réessayez : votre audio reste disponible sur cette page tant que vous ne la quittez pas.';
            errors.classList.remove('d-none');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i>Réessayer l’envoi';
        }
    });
})();
</script>
</body>
</html>
