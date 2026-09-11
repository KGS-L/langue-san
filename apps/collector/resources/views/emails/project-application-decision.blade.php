@php($approved = $status === \App\Enums\ProjectApplicationStatus::APPROVED)
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Candidature Langue SAN</title></head>
<body style="margin:0;background:#f6f4ee;font-family:Arial,sans-serif;color:#172640;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px;background:#f6f4ee;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fff;border-radius:18px;overflow:hidden;border:1px solid #e6e8ec;">
<tr><td style="padding:28px 32px;background:#172640;color:#fff;font-size:22px;font-weight:700;">Langue SAN</td></tr>
<tr><td style="padding:32px;">
<h1 style="margin:0 0 14px;font-size:26px;">{{ $approved ? 'Votre candidature est acceptée.' : 'Votre candidature a été examinée.' }}</h1>
<p style="margin:0 0 20px;color:#5b6678;line-height:1.65;">Bonjour {{ $name }},</p>
@if($approved)
<p style="margin:0 0 20px;color:#5b6678;line-height:1.65;">Merci d’avoir proposé votre expérience au projet Langue SAN. Votre candidature pour rejoindre le projet a été acceptée.</p>
@else
<p style="margin:0 0 20px;color:#5b6678;line-height:1.65;">Merci pour l’intérêt porté au projet. Votre candidature n’a pas été retenue à cette étape.</p>
@endif
@if($reason)
<div style="margin:22px 0;padding:16px 18px;border-radius:12px;background:#f8f5ee;color:#4c5668;line-height:1.6;"><strong>Message de l’équipe :</strong><br>{{ $reason }}</div>
@endif
<p style="margin:0 0 24px;color:#5b6678;line-height:1.65;">Connectez-vous à votre compte pour consulter le statut de votre candidature et les informations associées.</p>
<p style="margin:28px 0;text-align:center;"><a href="{{ $accountUrl }}" style="display:inline-block;background:#172640;color:#fff;text-decoration:none;font-weight:700;padding:14px 22px;border-radius:10px;">Se connecter à Langue SAN</a></p>
</td></tr>
</table>
</td></tr></table>
</body>
</html>
