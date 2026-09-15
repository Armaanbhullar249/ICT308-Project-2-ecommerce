<?php
require_once __DIR__.'/helpers.php';
try {
    db()->query('SELECT 1');
    respond([
        'ok'=>true,
        'message'=>'PHP backend and MySQL connection are working.',
        'store'=>'http://localhost/ICT308-Project-2-ecommerce-dev/home.html',
        'signup'=>'POST api/auth.php action=register',
    ]);
} catch (Throwable $e) {
    fail('Database connection failed. Check api/config/database.php and MySQL.',500);
}
