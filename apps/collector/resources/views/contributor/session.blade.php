<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Question {{ $progress['completed'] + 1 }} - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
    <style>
        .voice-card {
            border: 2px solid rgba(23, 38, 64, .32);
            background: linear-gradient(180deg, #f8f5ee 0%, #ffffff 100%);
            border-radius: 1rem;
        }
        .record-button {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            display: inline-grid;
            place-items: center;
            font-size: 1.8rem;
        }
        .recording-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            background: #dc3545;
            animation: pulse 1s infinite;
        }
        .recommended-badge {
            background: #172640;
            color: #fff;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .4; transform: scale(.8); }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="34" height="34" alt="Logo Langue SAN"> Langue SAN
        </a>
        <span class="small text-muted">Votre contribution</span>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Étape 3 sur 3 · {{ $session->category->name }}</span>
                    <span class="small fw-semibold">Question {{ $progress['completed'] + 1 }} / {{ $progress['total'] }}</span>
                </div>
                <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ $progress['percent'] }}%"></div></div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div id="clientErrors" class="alert alert-danger d-none"></div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-3">
                        <span class="badge bg-light text-dark border mb-2">{{ $sessionPrompt->prompt->type->value === 'word' ? 'Mot' : 'Phrase' }}</span>
                        <div class="small text-muted">Comment dites-vous ceci en San ?</div>
                    </div>

                    <h1 class="display-6 fw-bold mb-2">{{ $sessionPrompt->prompt->french_text }}</h1>
                    @if($sessionPrompt->prompt->context)
                        <div class="alert alert-light border"><strong>Précision :</strong> {{ $sessionPrompt->prompt->context }}</div>
                    @endif

                    <p class="small text-muted mb-4">Vous pouvez répondre avec votre voix, par écrit, ou avec les deux. Si vous n’êtes pas sûr, vous pouvez passer cette question.</p>

                    <form id="contributionForm" method="POST" enctype="multipart/form-data" action="{{ route('contributor.sessions.submit', [$session, $sessionPrompt]) }}">
                        @csrf

                        <div class="voice-card p-4 mb-4 text-center">
                            <div class="d-flex justify-content-center mb-2"><span class="badge recommended-badge">Recommandé</span></div>
                            <h2 class="h5 fw-bold"><i class="bi bi-mic-fill me-1"></i>Répondre avec votre voix</h2>
                            <p class="text-muted small mb-3">Appuyez sur le micro, parlez naturellement en San, puis arrêtez l’enregistrement. Vous pourrez vous réécouter avant d’envoyer.</p>

                            <div id="recordingIdle">
                                <button id="startRecording" type="button" class="btn btn-primary record-button" aria-label="Commencer l’enregistrement">
                                    <i class="bi bi-mic-fill"></i>
                                </button>
                                <div class="small text-muted mt-2">Appuyer pour enregistrer</div>
                            </div>

                            <div id="recordingActive" class="d-none">
                                <div class="mb-3"><span class="recording-dot me-2"></span><strong>Enregistrement en cours</strong> · <span id="recordingTimer">00:00</span></div>
                                <button id="stopRecording" type="button" class="btn btn-danger btn-lg rounded-pill px-4">
                                    <i class="bi bi-stop-fill me-1"></i>Arrêter
                                </button>
                            </div>

                            <div id="recordingReady" class="d-none">
                                <div class="alert alert-success py-2"><i class="bi bi-check-circle me-1"></i>Votre voix est enregistrée.</div>
                                <audio id="audioPreview" class="w-100 mb-3" controls></audio>
                                <button id="recordAgain" type="button" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-repeat me-1"></i>Recommencer l’enregistrement
                                </button>
                            </div>

                            <div id="microphoneMessage" class="small text-muted mt-3">Votre navigateur vous demandera l’autorisation d’utiliser le microphone.</div>
                            <input type="hidden" name="audio_duration_ms" id="audioDurationMs" value="">
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="flex-grow-1 border-top"></span>
                                <span class="small text-muted">ou écrire la réponse</span>
                                <span class="flex-grow-1 border-top"></span>
                            </div>
                            <label class="form-label fw-semibold" for="san_text">Réponse écrite en San</label>
                            <textarea class="form-control" id="san_text" name="san_text" rows="4" maxlength="5000" placeholder="Écrivez votre réponse ici…">{{ old('san_text') }}</textarea>
                            @if(!$profile->can_write_san)
                                <div class="form-text">Vous avez indiqué ne pas bien écrire le San : l’enregistrement vocal est suffisant.</div>
                            @endif
                        </div>

                        <button id="submitContribution" class="btn btn-primary btn-lg w-100" type="submit">
                            Enregistrer et continuer <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>

                    <form id="skipForm" method="POST" class="mt-2" action="{{ route('contributor.sessions.skip', [$session, $sessionPrompt]) }}">
                        @csrf
                        <button class="btn btn-link text-secondary w-100" type="submit">Je ne sais pas / passer cette question</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
(() => {
    const sessionId = {{ $session->id }};
    const questionId = {{ $sessionPrompt->id }};
    const draftKey = `langue_san_draft_${sessionId}_${questionId}`;
    const activeSessionKey = 'langue_san_active_session';
    const textarea = document.getElementById('san_text');
    const form = document.getElementById('contributionForm');
    const skipForm = document.getElementById('skipForm');
    const submitButton = document.getElementById('submitContribution');
    const clientErrors = document.getElementById('clientErrors');

    localStorage.setItem(activeSessionKey, JSON.stringify({
        sessionId,
        url: window.location.href,
        category: @json($session->category->name),
        completed: {{ $progress['completed'] }},
        total: {{ $progress['total'] }},
        updatedAt: new Date().toISOString(),
    }));

    const savedDraft = localStorage.getItem(draftKey);
    if (!textarea.value && savedDraft) {
        textarea.value = savedDraft;
    }
    textarea.addEventListener('input', () => localStorage.setItem(draftKey, textarea.value));

    let recorder = null;
    let stream = null;
    let chunks = [];
    let recordedBlob = null;
    let recordedMimeType = '';
    let startedAt = null;
    let durationMs = 0;
    let timerInterval = null;

    const idle = document.getElementById('recordingIdle');
    const active = document.getElementById('recordingActive');
    const ready = document.getElementById('recordingReady');
    const startButton = document.getElementById('startRecording');
    const stopButton = document.getElementById('stopRecording');
    const againButton = document.getElementById('recordAgain');
    const timer = document.getElementById('recordingTimer');
    const preview = document.getElementById('audioPreview');
    const micMessage = document.getElementById('microphoneMessage');
    const durationInput = document.getElementById('audioDurationMs');

    function formatTime(ms) {
        const totalSeconds = Math.floor(ms / 1000);
        const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');
        return `${minutes}:${seconds}`;
    }

    function preferredMimeType() {
        if (!window.MediaRecorder) return '';
        const types = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/ogg;codecs=opus',
            'audio/mp4',
        ];
        return types.find(type => MediaRecorder.isTypeSupported(type)) || '';
    }

    function releaseStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    async function startRecording() {
        clientErrors.classList.add('d-none');

        if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
            micMessage.textContent = 'Ce navigateur ne permet pas l’enregistrement vocal direct. Vous pouvez répondre par écrit.';
            micMessage.classList.add('text-danger');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            chunks = [];
            recordedBlob = null;
            durationMs = 0;
            durationInput.value = '';

            const mimeType = preferredMimeType();
            recorder = mimeType ? new MediaRecorder(stream, { mimeType }) : new MediaRecorder(stream);
            recordedMimeType = recorder.mimeType || mimeType || 'audio/webm';

            recorder.addEventListener('dataavailable', event => {
                if (event.data && event.data.size > 0) chunks.push(event.data);
            });

            recorder.addEventListener('stop', () => {
                durationMs = Math.max(0, Date.now() - startedAt);
                durationInput.value = String(durationMs);
                recordedBlob = new Blob(chunks, { type: recordedMimeType });

                if (preview.src) URL.revokeObjectURL(preview.src);
                preview.src = URL.createObjectURL(recordedBlob);

                clearInterval(timerInterval);
                releaseStream();
                active.classList.add('d-none');
                ready.classList.remove('d-none');
                micMessage.textContent = `Enregistrement prêt · ${formatTime(durationMs)}`;
                micMessage.classList.remove('text-danger');
            });

            recorder.start(250);
            startedAt = Date.now();
            timer.textContent = '00:00';
            timerInterval = setInterval(() => {
                timer.textContent = formatTime(Date.now() - startedAt);
            }, 500);

            idle.classList.add('d-none');
            ready.classList.add('d-none');
            active.classList.remove('d-none');
            micMessage.textContent = 'Parlez maintenant. Votre voix n’est envoyée qu’après avoir cliqué sur « Enregistrer et continuer ».';
        } catch (error) {
            releaseStream();
            micMessage.textContent = 'Impossible d’utiliser le microphone. Vérifiez que vous avez autorisé son accès dans le navigateur.';
            micMessage.classList.add('text-danger');
        }
    }

    function stopRecording() {
        if (recorder && recorder.state === 'recording') recorder.stop();
    }

    function resetRecording() {
        if (preview.src) URL.revokeObjectURL(preview.src);
        preview.removeAttribute('src');
        preview.load();
        recordedBlob = null;
        chunks = [];
        durationMs = 0;
        durationInput.value = '';
        ready.classList.add('d-none');
        idle.classList.remove('d-none');
        micMessage.textContent = 'Votre navigateur vous demandera l’autorisation d’utiliser le microphone.';
        micMessage.classList.remove('text-danger');
    }

    startButton.addEventListener('click', startRecording);
    stopButton.addEventListener('click', stopRecording);
    againButton.addEventListener('click', resetRecording);

    form.addEventListener('submit', async event => {
        event.preventDefault();
        clientErrors.classList.add('d-none');

        if (recorder && recorder.state === 'recording') {
            clientErrors.textContent = 'Arrêtez d’abord l’enregistrement avant de continuer.';
            clientErrors.classList.remove('d-none');
            return;
        }

        if (!textarea.value.trim() && !recordedBlob) {
            clientErrors.textContent = 'Enregistrez votre voix ou écrivez une réponse avant de continuer.';
            clientErrors.classList.remove('d-none');
            return;
        }

        const data = new FormData(form);
        if (recordedBlob) {
            const extension = recordedMimeType.includes('mp4') ? 'm4a' : (recordedMimeType.includes('ogg') ? 'ogg' : 'webm');
            data.append('audio', new File([recordedBlob], `reponse-${sessionId}-${questionId}.${extension}`, { type: recordedMimeType }));
            data.set('audio_duration_ms', String(durationMs));
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Enregistrement…';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: data,
                headers: {
                    'Accept': 'application/json, text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (response.status === 422) {
                const payload = await response.json();
                const messages = Object.values(payload.errors || {}).flat();
                clientErrors.innerHTML = messages.map(message => `<div>${message}</div>`).join('');
                clientErrors.classList.remove('d-none');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Enregistrer et continuer <i class="bi bi-arrow-right ms-1"></i>';
                return;
            }

            if (!response.ok) throw new Error('submit_failed');

            localStorage.removeItem(draftKey);
            window.location.href = response.url || @json(route('contributor.sessions.show', $session));
        } catch (error) {
            clientErrors.textContent = 'La réponse n’a pas pu être enregistrée. Vérifiez votre connexion et réessayez.';
            clientErrors.classList.remove('d-none');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Enregistrer et continuer <i class="bi bi-arrow-right ms-1"></i>';
        }
    });

    skipForm.addEventListener('submit', () => localStorage.removeItem(draftKey));
    window.addEventListener('beforeunload', releaseStream);
})();
</script>
</body>
</html>