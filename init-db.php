<?php
 require __DIR__ . '/src/bootstrap.php';
try {
    foreach (array_filter(array_map('trim', explode(';', file_get_contents(__DIR__ . '/schema.sql')))) as $sql) {
        db()->exec($sql);
    }
    echo "✅ Tables created in the online database.\n";
} catch (Throwable $e) {
    echo '❌ ' . $e->getMessage() . "\n";
}
