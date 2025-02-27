<!DOCTYPE html>
<html>
<head>
    <title>Reset Uw Wachtwoord</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: white; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #0f4c75; border-radius: 8px; overflow: hidden; color: #bbe1fa;">
        <div style="padding: 20px; background-color: #3282b8; text-align: center;">
            <h1 style="margin: 0; font-size: 24px; color: #ffffff;">Reset Uw Wachtwoord</h1>
        </div>
        <div style="padding: 20px;">
            <p style="font-size: 16px; margin-bottom: 20px; color: #bbe1fa;">Hallo {{ $user->first_name }},</p>
            <p style="font-size: 16px; margin-bottom: 20px; color: #bbe1fa;">
                U heeft recentelijk gevraagd om het wachtwoord van uw account opnieuw in te stellen. Klik op de knop hieronder om het wachtwoord te resetten.
            </p>
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #ffffff; background-color: #0f4c75; text-decoration: none; border-radius: 4px;">
                    Reset Wachtwoord
                </a>
            </div>
            <p style="font-size: 14px; color: #bbe1fa;">
                Als u dit niet heeft aangevraagd, kunt u deze e-mail veilig negeren. Als u vragen heeft, neem dan gerust contact op met onze klantenservice.
            </p>
        </div>
        <div style="padding: 10px; background-color: #3282b8; text-align: center; font-size: 12px; color: #ffffff;">
            <p style="margin: 0;">© Q-Pigeons</p>
        </div>
    </div>
</body>
</html>
