<?php

define('ROOT', dirname(__DIR__));
define('VIEWS', __DIR__ . '/views');

const MAX_MB = 5;

// Configure secure sessions for local development and Render HTTPS.
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
        );

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/*
|--------------------------------------------------------------------------
| ENVIRONMENT VARIABLES
|--------------------------------------------------------------------------
*/

function load_env(string $file): void
{
    if (!is_file($file)) {
        return;
    }

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if (
            $line === '' ||
            $line[0] === '#' ||
            !str_contains($line, '=')
        ) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value, " \t\"'");

        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

load_env(ROOT . '/.env');

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST'),
        getenv('DB_PORT') ?: 3306,
        getenv('DB_NAME')
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 20,
    ];

    if (getenv('DB_SSL') === 'true') {
        $options[PDO::MYSQL_ATTR_SSL_CA] =
            getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';

        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    try {
        $pdo = new PDO(
            $dsn,
            getenv('DB_USER'),
            getenv('DB_PASSWORD'),
            $options
        );
    } catch (PDOException $exception) {
        sleep(1);

        $pdo = new PDO(
            $dsn,
            getenv('DB_USER'),
            getenv('DB_PASSWORD'),
            $options
        );
    }

    return $pdo;
}

/*
|--------------------------------------------------------------------------
| DATABASE HELPERS
|--------------------------------------------------------------------------
*/

function q(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement;
}

function tx(callable $fn)
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $result = $fn($pdo);
        $pdo->commit();

        return $result;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

/*
|--------------------------------------------------------------------------
| GENERAL HELPERS
|--------------------------------------------------------------------------
*/

class UserError extends RuntimeException
{
}

function e($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function arr($value): array
{
    return $value === null
        ? []
        : (is_array($value) ? $value : [$value]);
}

function render(string $view, array $vars = []): void
{
    extract($vars);
    include VIEWS . '/' . $view . '.php';
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function not_found(): never
{
    http_response_code(404);
    echo 'Portfolio not found. <a href="/manage">Back</a>';
    exit;
}

/*
|--------------------------------------------------------------------------
| ACCOUNT HELPERS
|--------------------------------------------------------------------------
*/

function logged_in(): bool
{
    return isset($_SESSION['user']['id']);
}

function require_login(): void
{
    if (!logged_in()) {
        redirect('/login');
    }
}

function require_guest(): void
{
    if (logged_in()) {
        redirect('/manage');
    }
}

/*
|--------------------------------------------------------------------------
| CSRF PROTECTION
|--------------------------------------------------------------------------
*/

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $saved = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($submitted) ||
        !is_string($saved) ||
        $saved === '' ||
        !hash_equals($saved, $submitted)
    ) {
        http_response_code(403);
        exit('Invalid request. Please go back and try again.');
    }
}

/*
|--------------------------------------------------------------------------
| PROFILE PHOTO UPLOAD
|--------------------------------------------------------------------------
*/

function photo_data_url(): ?string
{
    $file = $_FILES['photo'] ?? null;

    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $sizeError = 'Profile picture must be ' . MAX_MB . 'MB or smaller.';

    if (in_array(
        $file['error'],
        [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE],
        true
    )) {
        throw new UserError($sizeError);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new UserError('The photo could not be uploaded.');
    }

    if ($file['size'] > MAX_MB * 1024 * 1024) {
        throw new UserError($sizeError);
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new UserError('Invalid photo upload.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    if (!in_array($mime, $allowedTypes, true)) {
        throw new UserError('Please choose a JPG, PNG or WEBP image.');
    }

    $contents = file_get_contents($file['tmp_name']);

    if ($contents === false) {
        throw new UserError('The photo could not be read.');
    }

    return 'data:' . $mime . ';base64,' . base64_encode($contents);
}

/*
|--------------------------------------------------------------------------
| PORTFOLIO CHILD TABLE CONFIGURATION
|--------------------------------------------------------------------------
*/

const CHILDREN = [
    [
        'education',
        ['school', 'degree', 'years'],
        ['edu_school', 'edu_degree', 'edu_years'],
    ],
    [
        'projects',
        ['title', 'description', 'link'],
        ['proj_title', 'proj_desc', 'proj_link'],
    ],
    [
        'experience',
        ['company', 'position', 'years', 'description'],
        ['exp_company', 'exp_position', 'exp_years', 'exp_desc'],
    ],
    [
        'social_links',
        ['label', 'url'],
        ['link_label', 'link_url'],
    ],
];

/*
|--------------------------------------------------------------------------
| SAVE EDUCATION, PROJECTS, EXPERIENCE, LINKS AND SKILLS
|--------------------------------------------------------------------------
*/

function save_children(PDO $pdo, int $id, array $data): void
{
    foreach (CHILDREN as [$table, $columns, $fields]) {
        $delete = $pdo->prepare(
            "DELETE FROM $table WHERE portfolio_id = ?"
        );

        $delete->execute([$id]);

        $lists = array_map(
            fn($field) => arr($data[$field] ?? null),
            $fields
        );

        $insert = $pdo->prepare(
            "INSERT INTO $table (portfolio_id, " .
            implode(',', $columns) .
            ") VALUES (?" . str_repeat(',?', count($columns)) . ")"
        );

        $rowCount = 0;

        foreach ($lists as $list) {
            $rowCount = max($rowCount, count($list));
        }

        for ($i = 0; $i < $rowCount; $i++) {
            $values = [];

            foreach ($lists as $list) {
                $values[] = trim((string) ($list[$i] ?? ''));
            }

            $hasContent = false;

            foreach ($values as $value) {
                if ($value !== '') {
                    $hasContent = true;
                    break;
                }
            }

            if (!$hasContent) {
                continue;
            }

            $insert->execute([$id, ...$values]);
        }
    }

    $deleteSkills = $pdo->prepare(
        'DELETE FROM skills WHERE portfolio_id = ?'
    );

    $deleteSkills->execute([$id]);

    $insertSkill = $pdo->prepare(
        'INSERT INTO skills (portfolio_id, name) VALUES (?, ?)'
    );

    $skills = array_filter(
        array_map(
            'trim',
            explode(',', (string) ($data['skills'] ?? ''))
        ),
        fn($skill) => $skill !== ''
    );

    foreach (array_unique($skills) as $skill) {
        $insertSkill->execute([$id, $skill]);
    }
}

/*
|--------------------------------------------------------------------------
| GET A PORTFOLIO AND ITS CHILD DATA
|--------------------------------------------------------------------------
*/

function get_portfolio(int $id): ?array
{
    $portfolio = q(
        'SELECT * FROM portfolios WHERE id = ?',
        [$id]
    )->fetch();

    if (!$portfolio) {
        return null;
    }

    $portfolio['template'] = (int) $portfolio['template'];

    $childTables = [
        'education',
        'skills',
        'projects',
        'experience',
        'social_links',
    ];

    foreach ($childTables as $table) {
        $portfolio[$table] = q(
            "SELECT * FROM $table WHERE portfolio_id = ? ORDER BY id",
            [$id]
        )->fetchAll();
    }

    return $portfolio;
}

/*
|--------------------------------------------------------------------------
| GLOBAL EXCEPTION HANDLER
|--------------------------------------------------------------------------
*/

set_exception_handler(function (Throwable $exception): void {
    if ($exception instanceof UserError) {
        http_response_code(400);
        $message = e($exception->getMessage());
    } else {
        error_log((string) $exception);

        http_response_code(500);
        $message = 'Something went wrong.';
    }

    echo $message . ' <a href="javascript:history.back()">Go back</a>';
});

/*
|--------------------------------------------------------------------------
| RESEND EMAIL HELPER
|--------------------------------------------------------------------------
*/

function send_resend_email(
    string $to,
    string $subject,
    string $html,
    string $text = ''
): bool {
    $apiKey = getenv('RESEND_API_KEY');
    $from = getenv('MAIL_FROM') ?: 'onboarding@resend.dev';

    if (!$apiKey) {
        throw new RuntimeException(
            'Email service is not configured. Check your .env file.'
        );
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL is not enabled.');
    }

    $payload = [
        'from' => $from,
        'to' => [$to],
        'subject' => $subject,
        'html' => $html,
    ];

    if ($text !== '') {
        $payload['text'] = $text;
    }

    $ch = curl_init('https://api.resend.com/emails');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        error_log('Resend connection failed: ' . $curlError);

        throw new RuntimeException(
            'Could not connect to the email service.'
        );
    }

    if ($status < 200 || $status >= 300) {
        error_log(
            'Resend returned HTTP ' . $status . ': ' . $response
        );

        throw new RuntimeException(
            'The email service could not send the message. Check the Resend dashboard logs.'
        );
    }

    return true;
}