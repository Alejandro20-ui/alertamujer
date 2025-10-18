<?php
header("Content-Type: application/json");

$host = $_ENV['MYSQLHOST'] ?? 'localhost';
$user = $_ENV['MYSQLUSER'] ?? 'root';
$pass = $_ENV['MYSQLPASSWORD'] ?? '';
$db   = $_ENV['MYSQLDATABASE'] ?? 'alertamujer';
$port = $_ENV['MYSQLPORT'] ?? 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
if ($usuario_id <= 0) {
    echo json_encode(["error" => "Usuario inválido"]);
    exit;
}

$sql = "SELECT id, nombre, apellidos, numero, vinculo, imagen
        FROM contactos_confianza
        WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$contactos = [];
while ($row = $result->fetch_assoc()) {
    $contactos[] = $row;
}

echo json_encode($contactos);

$stmt->close();
$conn->close();
?>
