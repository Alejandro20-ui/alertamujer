<?php
$host = getenv('MYSQLHOST') ?: ($_ENV['MYSQLHOST'] ?? 'localhost');
$user = getenv('MYSQLUSER') ?: ($_ENV['MYSQLUSER'] ?? 'root');
$pass = getenv('MYSQLPASSWORD') ?: ($_ENV['MYSQLPASSWORD'] ?? '');
$db   = getenv('MYSQLDATABASE') ?: ($_ENV['MYSQLDATABASE'] ?? 'alertamujer');
$port = getenv('MYSQLPORT') ?: ($_ENV['MYSQLPORT'] ?? 3306);

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die(json_encode(array("status"=>"error","message"=>"Error de conexión: " . $conn->connect_error)));
}

// Establecer charset UTF-8
$conn->set_charset("utf8mb4");
?>