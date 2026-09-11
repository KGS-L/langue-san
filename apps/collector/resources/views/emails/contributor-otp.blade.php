<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Votre code Langue SAN</title>
</head>
<body style="margin:0;background:#f6f4ee;font-family:Arial,sans-serif;color:#172640;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px;background:#f6f4ee;">
    <tr><td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e6e8ec;">
            <tr><td style="padding:28px 32px;background:#172640;color:#ffffff;font-size:22px;font-weight:700;">Langue SAN</td></tr>
            <tr><td style="padding:32px;">
                <h1 style="margin:0 0 14px;font-size:26px;line-height:1.25;">Votre code de connexion</h1>
                <p style="margin:0 0 24px;color:#5b6678;line-height:1.65;">Utilisez ce code pour accéder à votre espace contributeur. Aucun mot de passe n’est nécessaire.</p>
                <div style="text-align:center;margin:28px 0;padding:20px;border-radius:14px;background:#f8f5ee;font-size:34px;letter-spacing:8px;font-weight:800;color:#172640;">{{ $code }}</div>
                <p style="margin:0;color:#5b6678;line-height:1.65;">Ce code expire dans {{ $ttlMinutes }} minutes. Si vous n’êtes pas à l’origine de cette demande, vous pouvez ignorer cet email.</p>
            </td></tr>
            <tr><td style="padding:20px 32px;border-top:1px solid #eef0f3;color:#7a8494;font-size:12px;">Préserver · Documenter · Transmettre</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
