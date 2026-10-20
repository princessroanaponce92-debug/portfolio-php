
<?php
$error = $error ?? '';
$success = $success ?? '';
$resetLink = $resetLink ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | PortfolioGen</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #f4f5f9;
            color: #222;
        }

        .auth-card {
            width: 100%;
            max-width: 430px;
            padding: 35px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
        }

        h1 {
            margin: 0 0 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
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
            margin-top: 20px;
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

        .message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 8px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .error {
            background: #fff0f0;
            color: #a32121;
        }

        .success {
            background: #edf9f0;
            color: #216b35;
        }

        .reset-link-box {
            margin-top: 15px;
            padding: 15px;
            background: #fff8e7;
            border: 1px solid #f0d99c;
            border-radius: 8px;
            line-height: 1.6;
        }

        .reset-link-box strong {
            display: block;
            color: #805b00;
            margin-bottom: 5px;
        }

        .reset-link-box a {
            display: inline-block;
            margin-top: 8px;
            color: #315fe8;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #315fe8;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <main class="auth-card">
        <a class="back" href="/login">← Back to Login</a>

        <h1>Forgot your password?</h1>

        <p class="subtitle">
            Enter the email address associated with your PortfolioGen
            account. If an account matches, instructions will be provided
            to reset your password.
        </p>

        <?php if ($error !== ''): ?>
            <div class="message error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="message success" role="status">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($resetLink !== ''): ?>
            <div class="reset-link-box">
                <strong>Local testing only</strong>

                Email delivery has not been configured yet.
                Use this link to test the password-reset process.

                <a href="<?= e($resetLink) ?>">
                    Reset my password →
                </a>
            </div>
        <?php endif; ?>

        <form method="POST" action="/forgot-password">
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
                placeholder="Enter your registered email"
            >

            <button type="submit">Continue</button>
        </form>
    </main>
</body>
</html>
