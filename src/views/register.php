
<?php
$error = $error ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f5f9;
            color: #222;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            width: 100%;
            max-width: 430px;
            background: #fff;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5d8df;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #9bb8ff;
            border-color: #537be8;
        }

        button {
            width: 100%;
            margin-top: 23px;
            padding: 13px;
            background: #315fe8;
            color: white;
            border: 0;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #244ac0;
        }

        .error {
            background: #fff0f0;
            color: #a32121;
            border-radius: 8px;
            padding: 12px;
            margin: 15px 0;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        a {
            color: #315fe8;
            text-decoration: none;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <main class="auth-card">
        <a class="back" href="/">← Back to Home</a>

        <h1>Create your account</h1>
        <p class="subtitle">
            Register to create and manage your own portfolio.
        </p>

        <?php if ($error !== ''): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/register">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="username">Username</label>
            <input
                id="username"
                name="username"
                type="text"
                required
                maxlength="255"
                autocomplete="username"
                pattern="[A-Za-z0-9_.-]+"
                title="Use letters, numbers, underscores, periods, or hyphens."
            >

            <label for="email">Email address</label>
            <input
                id="email"
                name="email"
                type="email"
                required
                maxlength="255"
                autocomplete="email"
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                minlength="8"
                autocomplete="new-password"
            >

            <label for="password_confirmation">Confirm password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                minlength="8"
                autocomplete="new-password"
            >

            <button type="submit">Create Account</button>
        </form>

        <p class="footer">
            Already have an account?
            <a href="/login">Log in</a>
        </p>
    </main>
</body>
</html>
