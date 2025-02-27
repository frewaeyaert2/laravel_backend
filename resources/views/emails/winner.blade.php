<!DOCTYPE html>
<html>
<head>
    <title>Gefeliciteerd met het winnen van de veiling</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #0f4c75;
            border-radius: 8px;
            overflow: hidden;
            color: #bbe1fa;
        }
        .email-header {
            padding: 20px;
            background-color: #3282b8;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            color: #ffffff;
        }
        .email-body {
            padding: 20px;
            background-color: #0f4c75;
        }
        .email-body p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #bbe1fa;
        }
        .email-body .highlight {
            font-weight: bold;
            color: #ffffff;
        }
        .email-footer {
            padding: 10px;
            background-color: #3282b8;
            text-align: center;
            font-size: 12px;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Gefeliciteerd {{$winner_first_name}} {{$winner_last_name}}!</h1>
        </div>
        <div class="email-body">
            <p>
                U bent de winnaar van de veiling voor het item 
                <span class="highlight">{{ $itemable->name }}</span>.
            </p>
            <p>
                Uw winnende bod: <span class="highlight">€{{ number_format($highestBid->bid, 2, ',', '.') }}</span>.
            </p>
            <p>
                Om uw item op te halen, verzoeken we u vriendelijk het totaalbedrag over te maken naar het onderstaande rekeningnummer:
            </p>
            <p>
                <strong>IBAN:</strong> BE83 7380 2998 2015<br>
                <strong>BIC:</strong> KREDBEBB<br>
                <strong>Bank:</strong> KBC Bank<br>
                <strong>Adres:</strong> Torhoutsesteenweg 201, 8117 Zedelgem
            </p>
            <p>
                Zodra de betaling is ontvangen, zullen we contact met u opnemen voor de verdere afhandeling.
            </p>
            <p>
                Bedankt voor uw deelname aan onze veiling. Als u vragen heeft, neem dan gerust contact op met onze klantenservice.
            </p>
        </div>
        <div class="email-footer">
            <p>© {{ date('Y') }} Q-Pigeons. Alle rechten voorbehouden.</p>
        </div>
    </div>
</body>
</html>
