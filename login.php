<?php
header('Content-Type: application/json');

$host = $_ENV['MYSQLHOST'] ?? 'localhost';
$user = $_ENV['MYSQLUSER'] ?? 'root';
$pass = $_ENV['MYSQLPASSWORD'] ?? '';
$db   = $_ENV['MYSQLDATABASE'] ?? 'alertamujer';
$port = $_ENV['MYSQLPORT'] ?? 3306;
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit();
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
$numero = isset($_POST['numero']) ? trim($_POST['numero']) : '';

if (empty($nombre) || empty($apellidos) || empty($numero)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
    exit();
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ? AND apellidos = ? AND numero = ?");
$stmt->bind_param("sss", $nombre, $apellidos, $numero);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'idUsuario' => $row['id']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Credenciales incorrectas. Verifica tus datos.'
    ]);
}

$stmt->close();
$conn->close();
?>