<?php
require_once __DIR__.'/helpers.php';
try {
    db()->query('SELECT 1');
    $dbName = getenv('DB_NAME') ?: 'warners_electronics';
    respond([
        'ok'=>true,
        'message'=>'PHP backend and MySQL connection are working.',
        'store'=>'http://localhost/ICT308-Project-2-ecommerce-dev/home.html',
        'php'=>PHP_VERSION,
        'database'=>$dbName,
    ]);
} catch (Throwable $e) {
    fail('Database connection failed. Check api/config/database.php and MySQL.',500);
}
