<?php

use App\Services\DataRetentionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('data:purge-expired-audio', function () {
    $result = app(DataRetentionService::class)->purgeExpiredAudio();

    $this->info(sprintf(
        'Nettoyage terminé : %d audio(s) rejeté(s), %d audio(s) pending ancien(s) supprimé(s).',
        $result['rejected'],
        $result['stale_pending'],
    ));
})->purpose('Supprimer les audios arrivés à expiration selon la politique de conservation.');

Schedule::command('data:purge-expired-audio')->dailyAt('02:30')->withoutOverlapping();
