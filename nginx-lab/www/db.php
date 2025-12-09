<?php
$host = 'db';
$db = 'lab5_db';
$user = 'lab5_user';
$pass = 'lab5_pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>