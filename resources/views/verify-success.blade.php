<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-mail Geverifieerd</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1b262c;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #bbe1fa;
        }
        .container {
            max-width: 600px;
            width: 100%;
            background-color: #0f4c75;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .container h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .container p {
            font-size: 16px;
            margin-bottom: 15px;
            color: #bbe1fa;
        }
        .container .button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #4a90e2;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .container .button:hover {
            background-color: #3282b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Geverifieerd</h1>
        <p>Hallo {{ $name }}, uw e-mailadres is succesvol geverifieerd.</p>
        <p>U kunt nu handmatig inloggen op de website wanneer u klaar bent.</p>
    </div>
</body>
</html>
