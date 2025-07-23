<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BüA Einwahl</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="login-page">
    <div class="login-container">
        <h1>Admin Login</h1>

        <?php if (isset($login_error)): ?>
            <div class="error"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Anmelden</button>
        </form>

        <div class="back-link">
            <a href="../">← Zurück zur Einwahl</a>
        </div>
    </div>
</body>

</html>