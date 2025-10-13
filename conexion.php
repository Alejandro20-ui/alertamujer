<?php
header("Content-Type: application/json; charset=UTF-8");

try {
    $host = getenv('MYSQLHOST') ?: 'localhost';
    $user = getenv('MYSQLUSER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: '';
    $db   = getenv('MYSQLDATABASE') ?: 'alertamujer';
    $port = getenv('MYSQLPORT') ?: 3306;

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    // Crear conexión con PDO
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión: " . $e->getMessage()
    ]);
    exit();
}
?>
