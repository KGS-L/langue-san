<?php

return [
    // Les audios de contributions définitivement rejetées ne sont plus nécessaires au corpus.
    'rejected_audio_days' => (int) env('DATA_RETENTION_REJECTED_AUDIO_DAYS', 90),

    // Un audio resté sans traitement dans une contribution pending est purgé après 12 mois.
    'stale_pending_audio_days' => (int) env('DATA_RETENTION_STALE_PENDING_AUDIO_DAYS', 365),

    // Délai opérationnel cible pour les demandes nécessitant une intervention humaine.
    'privacy_request_target_days' => (int) env('DATA_PRIVACY_REQUEST_TARGET_DAYS', 30),
];
