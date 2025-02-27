<!DOCTYPE html>
<html>
<head>
    <title>Reset Wachtwoord</title>
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
        .form-container {
            max-width: 400px;
            width: 100%;
            background-color: #0f4c75;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .form-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #ffffff;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #bbe1fa;
            text-align: left;
        }
        .form-container input[type="password"],
        .form-container input[type="hidden"] {
            width: 90%;
            margin: 0 auto 20px;
            padding: 10px;
            border: 1px solid #bbe1fa;
            border-radius: 4px;
            font-size: 14px;
            background-color: #3282b8;
            color: #ffffff;
            display: block;
        }
        .form-container button {
            width: 90%;
            padding: 10px;
            font-size: 16px;
            color: #ffffff;
            background-color: #0f4c75;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #3282b8;
        }
        .form-container p {
            font-size: 12px;
            color: #bbe1fa;
            text-align: center;
            margin-top: 20px;
        }
        .form-container .success-message {
            text-align: center;
            color: #00ff00;
            font-size: 16px;
            margin-top: 20px;
            display: none;
        }
        .form-container .error-message {
            text-align: center;
            color: #ff0000;
            font-size: 16px;
            margin-top: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Reset Wachtwoord</h1>
        <form id="resetPasswordForm">
            @csrf
            <label for="password">Nieuw Wachtwoord:</label>
            <input type="password" name="password" required>

            <label for="password_confirmation">Bevestig Wachtwoord:</label>
            <input type="password" name="password_confirmation" required>

            <input type="hidden" name="email" value="{{ request('email') }}">

            <button type="submit">Reset Wachtwoord</button>
        </form>
        <p class="success-message" id="successMessage">Uw wachtwoord is succesvol gereset!</p>
        <p class="error-message" id="errorMessage">Er is een fout opgetreden bij het resetten van uw wachtwoord. Probeer het opnieuw.</p>
        <p>Als u problemen ondervindt, neem dan contact op met onze klantenservice. Q-Pigeons</p>
    </div>

    <script>
        document.getElementById('resetPasswordForm').addEventListener('submit', async function(event) {
            event.preventDefault(); // Prevent default form submission

            // Get the form data
            const formData = new FormData(this);

            try {
                // Send the request
                const response = await fetch('api/reset-password', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                });

                const result = await response.json();

                // Handle the response
                if (response.ok) {
                    // Show success message
                    document.getElementById('successMessage').style.display = 'block';
                    document.getElementById('errorMessage').style.display = 'none';

                    // Optionally clear the form
                    this.reset();
                } else {
                    // Show error message
                    document.getElementById('errorMessage').innerText = result.message || 'Er is een fout opgetreden.';
                    document.getElementById('errorMessage').style.display = 'block';
                    document.getElementById('successMessage').style.display = 'none';
                }
            } catch (error) {
                // Handle network errors
                document.getElementById('errorMessage').innerText = 'Er is een fout opgetreden bij de verbinding met de server.';
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('successMessage').style.display = 'none';
            }
        });
    </script>
</body>
</html>
