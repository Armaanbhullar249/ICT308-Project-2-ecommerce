<?php
require_once __DIR__.'/helpers.php';
try {
    db()->query('SELECT 1');
    respond(['ok'=>true,'message'=>'PHP backend and MySQL connection are working.']);
} catch (Throwable $e) {
    fail('Database connection failed. Check api/config/database.php and MySQL.',500);
}
