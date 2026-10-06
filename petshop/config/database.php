<?php
// config/database.php

$db_host     = 'localhost';
$db_name     = 'petstore_db';
$db_username = 'root';          // ← renamed
$db_password = '';              // ← renamed

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $db_username, $db_password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}