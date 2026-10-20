
<?php
$isLoggedIn = function_exists('logged_in')
    ? logged_in()
    : isset($_SESSION['user']['id']);

$username = $_SESSION['user']['username'] ?? 'My Account';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PortfolioGen</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">

    <style>
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .back-button {
            display: none;
            align-items: center;
            gap: 6px;
            padding: 8px 13px;
            border: 1px solid #d8dce5;
            border-radius: 8px;
            background: white;
            color: #333;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .back-button.visible {
            display: inline-flex;
        }

        .back-button:hover {
            background: #f4f5f9;
        }

        .profile-menu {
            position: relative;
            display: inline-block;
        }

        .profile-menu summary {
            cursor: pointer;
            list-style: none;
            font-weight: 600;
        }

        .profile-menu summary::-webkit-details-marker {
            display: none;
        }

        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            z-index: 1000;
            min-width: 180px;
            padding: 10px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        }

        .profile-dropdown a,
        .profile-dropdown button {
            display: block;
            width: 100%;
            padding: 10px;
            border: 0;
            background: transparent;
            color: #222;
            text-align: left;
            text-decoration: none;
            font: inherit;
            cursor: pointer;
            border-radius: 6px;
        }

        .profile-dropdown a:hover,
        .profile-dropdown button:hover {
            background: #f4f5f9;
        }

        .profile-dropdown form {
            margin: 0;
        }

        @media (max-width: 600px) {
            .nav {
                align-items: flex-start;
            }

            .nav-links {
                gap: 12px;
            }
        }
    </style>
</head>
<body>

<nav class="nav">
    <div class="nav-left">
        <button
            type="button"
            id="backButton"
            class="back-button"
            aria-label="Go back to the previous page"
            onclick="history.back()">
            ← Back
        </button>

        <a class="brand" href="/">✨ PortfolioGen</a>
    </div>

    <div class="nav-links">
        <a href="/">HOME</a>

        <?php if ($isLoggedIn): ?>
            <a href="/create">CREATE</a>
            <a href="/manage">MANAGE</a>
            <a href="/favorites">FAVORITES</a>

            <details class="profile-menu">
                <summary>
                    👤 <?= e($username) ?> ▾
                </summary>

                <div class="profile-dropdown">
                    <a href="/manage">My Portfolios</a>
                    <a href="/favorites">My Favorites</a>

                    <form method="POST" action="/logout">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrf_token()) ?>">
                        <button type="submit">Log Out</button>
                    </form>
                </div>
            </details>
        <?php else: ?>
            <a href="/login">LOGIN</a>
            <a href="/register">REGISTER</a>
        <?php endif; ?>
    </div>
</nav>

<script>
(function () {
    const backButton = document.getElementById('backButton');

    if (!backButton) return;

    // Show Back only when this page was reached from another page
    // in the same website during this browsing session.
    try {
        const currentUrl = new URL(window.location.href);
        const previousUrl = document.referrer
            ? new URL(document.referrer)
            : null;

        const cameFromThisSite =
            previousUrl &&
            previousUrl.origin === currentUrl.origin &&
            previousUrl.pathname !== currentUrl.pathname;

        if (cameFromThisSite) {
            backButton.classList.add('visible');
        }
    } catch (error) {
        // Keep Back hidden if the previous page cannot be verified.
    }
})();
</script>

<main class="container">
```