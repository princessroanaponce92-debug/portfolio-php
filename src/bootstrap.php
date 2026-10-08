<?php
define('ROOT', dirname(__DIR__));
define('VIEWS', __DIR__ . '/views');
const MAX_MB = 5;

 function load_env(string $file): void {
    if (!is_file($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\"'");
        if (getenv($k) === false) { putenv("$k=$v"); $_ENV[$k] = $v; }
    }
}
load_env(ROOT . '/.env');

 function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST'), getenv('DB_PORT') ?: 3306, getenv('DB_NAME'));
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 20,
    ];
    if (getenv('DB_SSL') === 'true') {
         $opts[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    try {
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), $opts);
    } catch (PDOException $e) {            
        sleep(1);
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), $opts);
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function tx(callable $fn) {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $result = $fn($pdo);
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

 
class UserError extends RuntimeException {}

function e($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function arr($v): array { return $v === null ? [] : (is_array($v) ? $v : [$v]); }

function render(string $view, array $vars = []): void {
    extract($vars);
    include VIEWS . '/' . $view . '.php';
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function not_found(): never {
    http_response_code(404);
    echo 'Portfolio not found. <a href="/manage">Back</a>';
    exit;
}

 
function photo_data_url(): ?string {
    $f = $_FILES['photo'] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    $tooBig = 'Profile picture must be ' . MAX_MB . 'MB or smaller.';
    if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) throw new UserError($tooBig);
    if ($f['error'] !== UPLOAD_ERR_OK) throw new UserError('The photo could not be uploaded.');
    if ($f['size'] > MAX_MB * 1024 * 1024) throw new UserError($tooBig);

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
        throw new UserError('Please choose a JPG, PNG or WEBP image.');
    }
    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($f['tmp_name']));
}

 
const CHILDREN = [
    ['education',    ['school', 'degree', 'years'],                         ['edu_school', 'edu_degree', 'edu_years']],
    ['projects',     ['title', 'description', 'link'],                      ['proj_title', 'proj_desc', 'proj_link']],
    ['experience',   ['company', 'position', 'years', 'description'],       ['exp_company', 'exp_position', 'exp_years', 'exp_desc']],
    ['social_links', ['label', 'url'],                                      ['link_label', 'link_url']],
];

function save_children(PDO $pdo, int $id, array $b): void {
    foreach (CHILDREN as [$table, $cols, $fields]) {
        $pdo->prepare("DELETE FROM $table WHERE portfolio_id = ?")->execute([$id]);
        $lists = array_map(fn($f) => arr($b[$f] ?? null), $fields);
        $ins = $pdo->prepare("INSERT INTO $table (portfolio_id, " . implode(',', $cols) . ") VALUES (?" . str_repeat(',?', count($cols)) . ")");
        for ($i = 0; $i < count($lists[0]); $i++) {
            if (trim((string)$lists[0][$i]) === '') continue;
            $vals = array_map(fn($l) => trim((string)($l[$i] ?? '')), $lists);
            $ins->execute([$id, ...$vals]);
        }
    }
    $pdo->prepare('DELETE FROM skills WHERE portfolio_id = ?')->execute([$id]);
    $ins = $pdo->prepare('INSERT INTO skills (portfolio_id, name) VALUES (?, ?)');
    foreach (array_filter(array_map('trim', explode(',', (string)($b['skills'] ?? '')))) as $s) {
        $ins->execute([$id, $s]);
    }
}

function get_portfolio(int $id): ?array {
    $p = q('SELECT * FROM portfolios WHERE id = ?', [$id])->fetch();
    if (!$p) return null;
    $p['template'] = (int)$p['template'];
    foreach (['education', 'skills', 'projects', 'experience', 'social_links'] as $t) {
        $p[$t] = q("SELECT * FROM $t WHERE portfolio_id = ? ORDER BY id", [$id])->fetchAll();
    }
    return $p;
}

 
set_exception_handler(function (Throwable $ex) {
    if ($ex instanceof UserError) {
        http_response_code(400);
        $msg = e($ex->getMessage());
    } else {
        error_log((string)$ex);
        http_response_code(500);
        $msg = 'Something went wrong.';
    }
    echo $msg . ' <a href="javascript:history.back()">Go back</a>';
});
