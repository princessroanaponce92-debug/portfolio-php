<?php

if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) return false;
}
require __DIR__ . '/../src/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$path   = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

// HOME + HEALTH (UptimeRobot)
if ($method === 'GET' && $path === '/')       { render('home'); exit; }
if (in_array($method, ['GET', 'HEAD'], true) && $path === '/health') { echo 'ok'; exit; }

// CREATE
if ($method === 'GET' && $path === '/create') { render('form', ['p' => null]); exit; }

if ($method === 'POST' && $path === '/portfolio/save') {
    $b = $_POST;
    $photo = photo_data_url();
    $id = tx(function (PDO $pdo) use ($b, $photo) {
        $pdo->prepare('INSERT INTO portfolios (full_name, email, phone, address, about, photo) VALUES (?,?,?,?,?,?)')
            ->execute([$b['full_name'] ?? '', $b['email'] ?? '', $b['phone'] ?? '', $b['address'] ?? '', $b['about'] ?? '', $photo]);
        $id = (int)$pdo->lastInsertId();
        save_children($pdo, $id, $b);
        return $id;
    });
    redirect("/portfolio/$id/templates");
}

// MANAGE
if ($method === 'GET' && $path === '/manage') {
    $list = q('SELECT id, full_name, email, template, created_at FROM portfolios ORDER BY id DESC')->fetchAll();
    render('manage', ['list' => $list]);
    exit;
}

// PER-PORTFOLIO ROUTES
if (preg_match('#^/portfolio/(\d+)/(templates|template|preview|edit|update|delete)$#', $path, $m)) {
    $id = (int)$m[1];
    $action = $m[2];

    if ($method === 'GET' && $action === 'templates') {
        $p = get_portfolio($id) ?? not_found();
        render('templates', ['p' => $p]); exit;
    }

    if ($method === 'POST' && $action === 'template') {
        $t = in_array((int)($_POST['template'] ?? 0), [1, 2, 3], true) ? (int)$_POST['template'] : 1;
        q('UPDATE portfolios SET template = ? WHERE id = ?', [$t, $id]);
        redirect("/portfolio/$id/preview");
    }

    if ($method === 'GET' && $action === 'preview') {
        $p = get_portfolio($id) ?? not_found();
        $t = in_array((int)($_GET['t'] ?? 0), [1, 2, 3], true) ? (int)$_GET['t'] : $p['template'];
        render("templates/t$t", ['p' => $p, 't' => $t, 'previewing' => $t !== $p['template'], 'embed' => !empty($_GET['embed'])]);
        exit;
    }

    if ($method === 'GET' && $action === 'edit') {
        $p = get_portfolio($id) ?? not_found();
        render('form', ['p' => $p]); exit;
    }

    if ($method === 'POST' && $action === 'update') {
        $b = $_POST;
        $photo = photo_data_url();
        $set  = 'full_name=?, email=?, phone=?, address=?, about=?';
        $vals = [$b['full_name'] ?? '', $b['email'] ?? '', $b['phone'] ?? '', $b['address'] ?? '', $b['about'] ?? ''];
        if ($photo) { $set .= ', photo=?'; $vals[] = $photo; }
        elseif (($b['remove_photo'] ?? '') === '1') { $set .= ', photo=NULL'; }

        tx(function (PDO $pdo) use ($set, $vals, $id, $b) {
            $pdo->prepare("UPDATE portfolios SET $set WHERE id=?")->execute([...$vals, $id]);
            save_children($pdo, $id, $b);
        });
        redirect("/portfolio/$id/preview");
    }

    if ($method === 'POST' && $action === 'delete') {
        q('DELETE FROM portfolios WHERE id = ?', [$id]);
        redirect('/manage');
    }
}

http_response_code(404);
echo 'Page not found. <a href="/">Home</a>';
