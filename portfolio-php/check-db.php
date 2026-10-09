<?php
// Run with: php check-db.php
require __DIR__ . '/src/bootstrap.php';
try {
    echo "\nTABLES IN THE ONLINE DATABASE:\n";
    foreach (q('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) echo " - $t\n";

    echo "\nSAVED PORTFOLIOS:\n";
    foreach (q('SELECT id, full_name, email, template, created_at FROM portfolios')->fetchAll() as $r) {
        echo implode(' | ', $r) . "\n";
    }
    echo "\n";
    foreach (['education', 'skills', 'projects', 'experience', 'social_links'] as $t) {
        echo "$t: " . q("SELECT COUNT(*) AS total FROM $t")->fetch()['total'] . " row(s)\n";
    }
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
