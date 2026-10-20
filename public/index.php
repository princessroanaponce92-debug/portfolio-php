<?php

if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $requestPath;

    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../src/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

/*
|--------------------------------------------------------------------------
| AUTHENTICATION HELPERS
|--------------------------------------------------------------------------
*/

$userId = static function (): int {
    require_login();
    return (int) $_SESSION['user']['id'];
};

$checkName = static function (string $name): bool {
    return $name !== '' &&
        (bool) preg_match("/^[\p{L}\p{M} .'-]+$/u", $name);
};

/*
|--------------------------------------------------------------------------
| PORTFOLIO VALIDATION
|--------------------------------------------------------------------------
*/

$validatePortfolio = static function (array $b) use ($checkName): array {
    $data = [
        'full_name' => trim((string) ($b['full_name'] ?? '')),
        'email'     => trim((string) ($b['email'] ?? '')),
        'phone'     => trim((string) ($b['phone'] ?? '')),
        'address'   => trim((string) ($b['address'] ?? '')),
        'about'     => trim((string) ($b['about'] ?? '')),
        'skills'    => trim((string) ($b['skills'] ?? '')),
    ];

    if (!$checkName($data['full_name'])) {
        throw new UserError(
            'Enter a valid full name using letters only.'
        );
    }

    if (strlen($data['full_name']) > 255) {
        throw new UserError('Full name is too long.');
    }

    if (
        !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ||
        strlen($data['email']) > 255
    ) {
        throw new UserError('Enter a valid email address.');
    }

    $requiredFields = [
        'phone'   => 'Contact number',
        'address' => 'Address',
        'about'   => 'About Me',
        'skills'  => 'Skills',
    ];

    foreach ($requiredFields as $field => $label) {
        if ($data[$field] === '') {
            throw new UserError($label . ' is required.');
        }
    }

    if (strlen($data['phone']) > 30) {
        throw new UserError('Contact number is too long.');
    }

    if (strlen($data['address']) > 255) {
        throw new UserError('Address is too long.');
    }

    if (strlen($data['about']) > 5000) {
        throw new UserError('About Me is too long.');
    }

    if (strlen($data['skills']) > 2000) {
        throw new UserError('Skills information is too long.');
    }

    return $data;
};

/*
|--------------------------------------------------------------------------
| HOME AND UPTIMEROBOT HEALTH CHECK
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && $path === '/') {
    render('home');
    exit;
}

if (in_array($method, ['GET', 'HEAD'], true) && $path === '/health') {
    echo 'ok';
    exit;
}

/*
|--------------------------------------------------------------------------
| REGISTER
|--------------------------------------------------------------------------
*/

if ($path === '/register') {
    require_guest();

    if ($method === 'GET') {
        $error = '';
        render('register', compact('error'));
        exit;
    }

    if ($method === 'POST') {
        verify_csrf();

        $username = trim((string) ($_POST['username'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');

        if (
            $username === '' ||
            !preg_match('/^[A-Za-z0-9_.-]+$/', $username)
        ) {
            $error = 'Use a valid username with letters, numbers, dots, underscores, or hyphens.';
        } elseif (strlen($username) > 255) {
            $error = 'Username is too long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($email) > 255) {
            $error = 'Email address is too long.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $exists = q(
                'SELECT id FROM users
                 WHERE email = ? OR username = ?
                 LIMIT 1',
                [$email, $username]
            )->fetch();

            if ($exists) {
                $error = 'That email address or username is already registered.';
            } else {
                $newUserId = tx(
                    function (PDO $pdo) use ($username, $email, $password): int {
                        $stmt = $pdo->prepare(
                            'INSERT INTO users (username, email, password)
                             VALUES (?, ?, ?)'
                        );

                        $stmt->execute([
                            $username,
                            $email,
                            password_hash($password, PASSWORD_DEFAULT),
                        ]);

                        return (int) $pdo->lastInsertId();
                    }
                );

                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id' => $newUserId,
                    'username' => $username,
                    'email' => $email,
                ];

                redirect('/manage');
            }
        }

        render('register', compact('error'));
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if ($path === '/login') {
    require_guest();

    if ($method === 'GET') {
        $error = '';
        render('login', compact('error'));
        exit;
    }

    if ($method === 'POST') {
        verify_csrf();

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        $user = q(
            'SELECT id, username, email, password
             FROM users
             WHERE email = ?
             LIMIT 1',
            [$email]
        )->fetch();

        if (
            !$user ||
            !password_verify($password, (string) $user['password'])
        ) {
            $error = 'Incorrect email or password.';
            render('login', compact('error'));
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
        ];

        redirect('/manage');
    }
}

/*
|--------------------------------------------------------------------------
| FORGOT PASSWORD — RESEND EMAIL DELIVERY
|--------------------------------------------------------------------------
*/

if ($path === '/forgot-password') {
    if ($method === 'GET') {
        render('forgot-password', [
            'error' => '',
            'success' => '',
            'resetLink' => '',
        ]);
        exit;
    }

    if ($method === 'POST') {
        verify_csrf();

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $error = '';
        $success = '';
        $resetLink = '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $user = q(
                'SELECT id FROM users WHERE email = ? LIMIT 1',
                [$email]
            )->fetch();

            // Keep the response identical for existing and unknown accounts.
            $success = 'If an account exists for that email, '
                . 'password reset instructions will be sent.';

            if ($user) {
                $resetUserId = (int) $user['id'];

                // Remove older reset tokens for this account.
                q(
                    'DELETE FROM password_resets WHERE user_id = ?',
                    [$resetUserId]
                );

                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);

                // Reset links expire after 30 minutes.
                q(
                    'INSERT INTO password_resets '
                    . '(user_id, token_hash, expires_at) '
                    . 'VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))',
                    [$resetUserId, $tokenHash]
                );

                // Use the deployed URL when APP_URL is configured.
                $appUrl = rtrim(
                    getenv('APP_URL') ?: 'http://localhost:8000',
                    '/'
                );

                $resetLink = $appUrl
                    . '/reset-password?token='
                    . urlencode($token);

                $safeResetLink = e($resetLink);

                $subject = 'Reset your PortfolioGen password';

                $html = '
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport"
                              content="width=device-width, initial-scale=1.0">
                        <title>Password Reset</title>
                    </head>
                    <body style="font-family: Arial, sans-serif;
                                 line-height: 1.6;
                                 color: #222;
                                 max-width: 600px;
                                 margin: 0 auto;
                                 padding: 24px;">
                        <h2>Reset your PortfolioGen password</h2>

                        <p>
                            We received a request to reset your password.
                        </p>

                        <p>
                            Click the button below to choose a new password.
                            This link expires in 30 minutes and can only be
                            used once.
                        </p>

                        <p style="margin: 28px 0;">
                            <a href="' . $safeResetLink . '"
                               style="display: inline-block;
                                      background: #2563eb;
                                      color: #ffffff;
                                      text-decoration: none;
                                      padding: 12px 22px;
                                      border-radius: 6px;">
                                Reset My Password
                            </a>
                        </p>

                        <p>
                            If the button does not work, copy and paste
                            this link into your browser:
                        </p>

                        <p style="word-break: break-all;">
                            <a href="' . $safeResetLink . '">'
                                . $safeResetLink .
                            '</a>
                        </p>

                        <p>
                            If you did not request this password reset,
                            you can ignore this email.
                        </p>

                        <p>— PortfolioGen</p>
                    </body>
                    </html>
                ';

                $text = "Reset your PortfolioGen password\n\n"
                    . "Open this link to choose a new password:\n"
                    . $resetLink . "\n\n"
                    . "This link expires in 30 minutes and can only "
                    . "be used once.\n\n"
                    . "If you did not request this, ignore this email.";

                try {
                    send_resend_email(
                        $email,
                        $subject,
                        $html,
                        $text
                    );
                } catch (Throwable $exception) {
                    // Log details server-side without exposing them to visitors.
                    error_log(
                        'Password reset email failed: '
                        . $exception->getMessage()
                    );

                    // Do not leave a reset token that the user cannot receive.
                    q(
                        'DELETE FROM password_resets WHERE token_hash = ?',
                        [$tokenHash]
                    );
                }
            }
        }

        // Never display a reset token or link on this page.
        render('forgot-password', [
            'error' => $error,
            'success' => $success,
            'resetLink' => '',
        ]);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| RESET PASSWORD
|--------------------------------------------------------------------------
*/

if ($path === '/reset-password') {
    if (!in_array($method, ['GET', 'POST'], true)) {
        http_response_code(405);
        header('Allow: GET, POST');
        echo 'Method not allowed.';
        exit;
    }

    $token = trim((string) (
        $_POST['token'] ?? $_GET['token'] ?? ''
    ));

    $error = '';

    if (
        $token === '' ||
        !preg_match('/^[a-f0-9]{64}$/', $token)
    ) {
        render('reset-password', [
            'error' => 'This reset link is invalid or has expired. Please request a new one.',
            'token' => '',
        ]);
        exit;
    }

    $reset = q(
        'SELECT user_id FROM password_resets
         WHERE token_hash = ? AND expires_at > NOW()
         LIMIT 1',
        [hash('sha256', $token)]
    )->fetch();

    if (!$reset) {
        render('reset-password', [
            'error' => 'This reset link is invalid or has expired. Please request a new one.',
            'token' => '',
        ]);
        exit;
    }

    if ($method === 'GET') {
        render('reset-password', [
            'error' => '',
            'token' => $token,
        ]);
        exit;
    }

    verify_csrf();

    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($password) < 8) {
        $error = 'Your password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'The passwords do not match.';
    }

    if ($error !== '') {
        render('reset-password', [
            'error' => $error,
            'token' => $token,
        ]);
        exit;
    }

    $updated = tx(
        function (PDO $pdo) use ($reset, $token, $password): bool {
            $stmt = $pdo->prepare(
                'SELECT user_id FROM password_resets
                 WHERE token_hash = ? AND expires_at > NOW()
                 LIMIT 1 FOR UPDATE'
            );

            $stmt->execute([hash('sha256', $token)]);
            $validReset = $stmt->fetch();

            if (
                !$validReset ||
                (int) $validReset['user_id'] !== (int) $reset['user_id']
            ) {
                return false;
            }

            $stmt = $pdo->prepare(
                'UPDATE users SET password = ? WHERE id = ?'
            );

            $stmt->execute([
                password_hash($password, PASSWORD_DEFAULT),
                (int) $validReset['user_id'],
            ]);

            $stmt = $pdo->prepare(
                'DELETE FROM password_resets WHERE user_id = ?'
            );

            $stmt->execute([(int) $validReset['user_id']]);

            return true;
        }
    );

    if (!$updated) {
        render('reset-password', [
            'error' => 'This reset link is invalid or has expired. Please request a new one.',
            'token' => '',
        ]);
        exit;
    }

    redirect('/login');
}

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

if ($method === 'POST' && $path === '/logout') {
    require_login();
    verify_csrf();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => $cookie['samesite'] ?? 'Lax',
            ]
        );
    }

    session_destroy();
    redirect('/login');
}

/*
|--------------------------------------------------------------------------
| CREATE PORTFOLIO FORM
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && $path === '/create') {
    $userId();

    render('form', ['p' => null]);
    exit;
}

/*
|--------------------------------------------------------------------------
| SAVE NEW PORTFOLIO
|--------------------------------------------------------------------------
*/

if ($method === 'POST' && $path === '/portfolio/save') {
    $uid = $userId();
    verify_csrf();

    $b = $_POST;
    $data = $validatePortfolio($b);
    $b = array_merge($b, $data);

    $photo = photo_data_url();

    $id = tx(
        function (PDO $pdo) use ($data, $b, $photo, $uid): int {
            $stmt = $pdo->prepare(
                'INSERT INTO portfolios
                    (user_id, full_name, email, phone, address, about, photo)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $uid,
                $data['full_name'],
                $data['email'],
                $data['phone'],
                $data['address'],
                $data['about'],
                $photo,
            ]);

            $id = (int) $pdo->lastInsertId();

            save_children($pdo, $id, $b);

            return $id;
        }
    );

    redirect("/portfolio/$id/templates");
}

/*
|--------------------------------------------------------------------------
| MANAGE MY PORTFOLIOS
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && $path === '/manage') {
    $uid = $userId();

    $list = q(
        'SELECT id, full_name, email, template, created_at
         FROM portfolios
         WHERE user_id = ?
         ORDER BY id DESC',
        [$uid]
    )->fetchAll();

    $favoriteIds = q(
        'SELECT portfolio_id
         FROM favorites
         WHERE user_id = ?',
        [$uid]
    )->fetchAll(PDO::FETCH_COLUMN);

    render('manage', [
        'list' => $list,
        'favoriteIds' => array_map('intval', $favoriteIds),
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| FAVORITES PAGE
|--------------------------------------------------------------------------
*/

if ($method === 'GET' && $path === '/favorites') {
    $uid = $userId();

    $list = q(
        'SELECT p.id, p.full_name, p.email, p.template, p.created_at
         FROM favorites f
         INNER JOIN portfolios p ON p.id = f.portfolio_id
         WHERE f.user_id = ? AND p.user_id = ?
         ORDER BY f.created_at DESC',
        [$uid, $uid]
    )->fetchAll();

    $favoriteIds = array_map(
        'intval',
        array_column($list, 'id')
    );

    render('manage', [
        'list' => $list,
        'favoriteIds' => $favoriteIds,
        'favoritesPage' => true,
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| ADD OR REMOVE A FAVORITE
|--------------------------------------------------------------------------
*/

if (
    $method === 'POST' &&
    preg_match('#^/portfolio/(\d+)/favorite$#', $path, $m)
) {
    $uid = $userId();
    verify_csrf();

    $id = (int) $m[1];

    $owned = q(
        'SELECT id
         FROM portfolios
         WHERE id = ? AND user_id = ?
         LIMIT 1',
        [$id, $uid]
    )->fetch();

    if (!$owned) {
        not_found();
    }

    $exists = q(
        'SELECT portfolio_id
         FROM favorites
         WHERE user_id = ? AND portfolio_id = ?
         LIMIT 1',
        [$uid, $id]
    )->fetch();

    if ($exists) {
        q(
            'DELETE FROM favorites
             WHERE user_id = ? AND portfolio_id = ?',
            [$uid, $id]
        );
    } else {
        q(
            'INSERT INTO favorites (user_id, portfolio_id)
             VALUES (?, ?)',
            [$uid, $id]
        );
    }

    $returnTo = ($_POST['return_to'] ?? '') === '/favorites'
        ? '/favorites'
        : '/manage';

    redirect($returnTo);
}

/*
|--------------------------------------------------------------------------
| PORTFOLIO TEMPLATE, PREVIEW, EDIT, UPDATE AND DELETE
|--------------------------------------------------------------------------
*/

if (
    preg_match(
        '#^/portfolio/(\d+)/(templates|template|preview|edit|update|delete)$#',
        $path,
        $m
    )
) {
    $uid = $userId();
    $id = (int) $m[1];
    $action = $m[2];

    $owned = q(
        'SELECT id
         FROM portfolios
         WHERE id = ? AND user_id = ?
         LIMIT 1',
        [$id, $uid]
    )->fetch();

    if (!$owned) {
        not_found();
    }

    /*
    | Template selection page
    */

    if ($method === 'GET' && $action === 'templates') {
        $p = get_portfolio($id) ?? not_found();

        render('templates', ['p' => $p]);
        exit;
    }

    /*
    | Save selected template
    */

    if ($method === 'POST' && $action === 'template') {
        verify_csrf();

        $template = (int) ($_POST['template'] ?? 0);

        if (!in_array($template, [1, 2, 3], true)) {
            throw new UserError('Please choose a valid template.');
        }

        q(
            'UPDATE portfolios
             SET template = ?
             WHERE id = ? AND user_id = ?',
            [$template, $id, $uid]
        );

        redirect("/portfolio/$id/preview");
    }

    /*
    | Preview portfolio
    */

    if ($method === 'GET' && $action === 'preview') {
        $p = get_portfolio($id) ?? not_found();

        $template = in_array(
            (int) ($_GET['t'] ?? 0),
            [1, 2, 3],
            true
        )
            ? (int) $_GET['t']
            : (int) $p['template'];

        render("templates/t$template", [
            'p' => $p,
            't' => $template,
            'previewing' => $template !== (int) $p['template'],
            'embed' => !empty($_GET['embed']),
        ]);

        exit;
    }

    /*
    | Edit portfolio form
    */

    if ($method === 'GET' && $action === 'edit') {
        $p = get_portfolio($id) ?? not_found();

        render('form', ['p' => $p]);
        exit;
    }

    /*
    | Update existing portfolio
    */

    if ($method === 'POST' && $action === 'update') {
        verify_csrf();

        $b = $_POST;
        $data = $validatePortfolio($b);
        $b = array_merge($b, $data);

        $photo = photo_data_url();

        $set = 'full_name = ?, email = ?, phone = ?, address = ?, about = ?';

        $values = [
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['about'],
        ];

        if ($photo !== null) {
            $set .= ', photo = ?';
            $values[] = $photo;
        } elseif (($b['remove_photo'] ?? '') === '1') {
            $set .= ', photo = NULL';
        }

        tx(
            function (PDO $pdo) use ($set, $values, $id, $b, $uid): void {
                $stmt = $pdo->prepare(
                    "UPDATE portfolios
                     SET $set
                     WHERE id = ? AND user_id = ?"
                );

                $stmt->execute([...$values, $id, $uid]);

                save_children($pdo, $id, $b);
            }
        );

        redirect("/portfolio/$id/preview");
    }

    /*
    | Delete portfolio
    */

    if ($method === 'POST' && $action === 'delete') {
        verify_csrf();

        q(
            'DELETE FROM portfolios
             WHERE id = ? AND user_id = ?',
            [$id, $uid]
        );

        redirect('/manage');
    }
}

/*
|--------------------------------------------------------------------------
| NOT FOUND
|--------------------------------------------------------------------------
*/

http_response_code(404);
echo 'Page not found. <a href="/">Home</a>';
