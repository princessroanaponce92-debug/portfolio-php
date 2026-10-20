
<?php
$error = $error ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>

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
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
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

        a:hover {
            text-decoration: underline;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <main class="auth-card">

        <a class="back" href="/">← Back to Home</a>

        <h1>Welcome back</h1>

        <p class="subtitle">
            Log in to manage your portfolio.
        </p>

        <?php if ($error !== ''): ?>
            <div class="error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="email">Email address</label>

            <input
                id="email"
                name="email"
                type="email"
                required
                autocomplete="email"
            >

            <label for="password">Password</label>

            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
            >

            <div class="forgot-password">
                <a href="/forgot-password">
                    Forgot my password?
                </a>
            </div>

            <button type="submit">Log In</button>

        </form>

        <p class="footer">
            Don't have an account?
            <a href="/register">Create one</a>
        </p>

    </main>
</body>
</html>
