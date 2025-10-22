<?php
header("Content-Type: application/json");

$host = $_ENV['MYSQLHOST'] ?? 'localhost';
$user = $_ENV['MYSQLUSER'] ?? 'root';
$pass = $_ENV['MYSQLPASSWORD'] ?? '';
$db   = $_ENV['MYSQLDATABASE'] ?? 'alertamujer';
$port = $_ENV['MYSQLPORT'] ?? 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit;
}

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
if ($usuario_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "ID de usuario inválido"]);
    exit;
}

$sql = "SELECT id, nombre, apellidos, numero, vinculo, imagen
        FROM contactos_confianza
        WHERE idUsuario = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Error al preparar la consulta"]);
    exit;
}

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