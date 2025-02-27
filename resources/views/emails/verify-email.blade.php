<!DOCTYPE html>
<html>
<head>
    <title>Verifieer Uw E-mailadres</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: white; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #0f4c75; border-radius: 8px; overflow: hidden; color: #bbe1fa;">
        <div style="padding: 20px; background-color: #3282b8; text-align: center;">
            <h1 style="margin: 0; font-size: 24px; color: #ffffff;">Verifieer Uw E-mailadres</h1>
        </div>
        <div style="padding: 20px;">
            <p style="font-size: 16px; margin-bottom: 20px; color: #bbe1fa;">Hallo {{ $user->name }},</p>
            <p style="font-size: 16px; margin-bottom: 20px; color: #bbe1fa;">
                Klik op de knop hieronder om uw e-mailadres te verifiëren en uw account te activeren.
            </p>
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="{{ $url }}" style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #ffffff; background-color: #0f4c75; text-decoration: none; border-radius: 4px;">
                    Verifieer E-mailadres
                </a>
            </div>
            <p style="font-size: 14px; color: #bbe1fa;">
                Als u dit account niet heeft aangemaakt, is geen verdere actie vereist. Als u vragen heeft, neem dan gerust contact op met onze klantenservice.
            </p>
        </div>
        <div style="padding: 10px; background-color: #3282b8; text-align: center; font-size: 12px; color: #ffffff;">
            <p style="margin: 0;">© Q-Pigeons</p>
        </div>
    </div>
</body>
</html>
