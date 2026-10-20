
<?php
$error = $error ?? '';
$token = $token ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | PortfolioGen</title>

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
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin: 0 0 10px;
            font-size: 28px;
        }

        .subtitle {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.6;
        }

        label {
            display: block;
            margin: 18px 0 8px;
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
            outline: 2px solid #315fe8;
            outline-offset: 2px;
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 13px;
            border: 0;
            border-radius: 8px;
            background: #315fe8;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #244ac0;
        }

        .message {
            margin: 18px 0;
            padding: 12px;
            border-radius: 8px;
            line-height: 1.5;
        }

        .error {
            background: #fff0f0;
            color: #a32121;
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

        .hint {
            margin-top: 8px;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <main class="auth-card">
        <a class="back" href="/forgot-password">
            ← Request another reset link
        </a>

        <h1>Reset your password</h1>

        <p class="subtitle">
            Create a new password for your PortfolioGen account.
            Your password must contain at least 8 characters.
        </p>

        <?php if ($error !== ''): ?>
            <div class="message error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($token !== ''): ?>
            <form method="POST" action="/reset-password">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <input
                    type="hidden"
                    name="token"
                    value="<?= e($token) ?>"
                >

                <label for="password">New password</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Enter your new password"
                >

                <p class="hint">
                    Use at least 8 characters.
                </p>

                <label for="confirm_password">
                    Confirm new password
                </label>

                <input
                    id="confirm_password"
                    name="confirm_password"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Enter your new password again"
                >

                <button type="submit">
                    Save New Password
                </button>
            </form>
        <?php else: ?>
            <p>
                Request a new reset link to continue.
            </p>
        <?php endif; ?>
    </main>
</body>
</html>
