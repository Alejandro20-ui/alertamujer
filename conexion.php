<?php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST') ?: ($_ENV['MYSQLHOST'] ?? 'localhost');
$user = getenv('MYSQLUSER') ?: ($_ENV['MYSQLUSER'] ?? 'root');
$pass = getenv('MYSQLPASSWORD') ?: ($_ENV['MYSQLPASSWORD'] ?? '');
$db   = getenv('MYSQLDATABASE') ?: ($_ENV['MYSQLDATABASE'] ?? 'alertamujer');
$port = getenv('MYSQLPORT') ?: ($_ENV['MYSQLPORT'] ?? 3306);

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Error de conexión: " . $conn->connect_error
    ]));
}

// Charset
$conn->set_charset("utf8mb4");
?>
