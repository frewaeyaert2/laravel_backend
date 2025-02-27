<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifieer Uw E-mailadres</title>
</head>
<body>
    <h1>Verifieer Uw E-mailadres</h1>
    <p>Hallo {{ $user->name }},</p>
    <p>Klik op de knop hieronder om uw e-mailadres te verifiëren:</p>
    <a href="{{ $url }}">Verifieer E-mailadres</a>
    <p>Als u dit account niet heeft aangemaakt, kunt u deze e-mail negeren.</p>
</body>
</html>
