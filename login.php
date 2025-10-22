<?php
header('Content-Type: application/json');
error_reporting(0);

$host = $_ENV['MYSQLHOST'] ?? 'localhost';
$user = $_ENV['MYSQLUSER'] ?? 'root';
$pass = $_ENV['MYSQLPASSWORD'] ?? '';
$db   = $_ENV['MYSQLDATABASE'] ?? 'alertamujer';
$port = $_ENV['MYSQLPORT'] ?? 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$numero = trim($_POST['numero'] ?? '');

if (empty($nombre) || empty($apellidos) || empty($numero)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
    exit();
}

if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $nombre)) {
    echo json_encode(['status' => 'error', 'message' => 'El nombre contiene caracteres inválidos.']);
    exit();
}

if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $apellidos)) {
    echo json_encode(['status' => 'error', 'message' => 'El apellido contiene caracteres inválidos.']);
    exit();
}

if (!preg_match('/^[0-9]{9}$/', $numero)) {
    echo json_encode(['status' => 'error', 'message' => 'El número debe tener 9 dígitos numéricos.']);
    exit();
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ? AND apellidos = ? AND numero = ?");
$stmt->bind_param("sss", $nombre, $apellidos, $numero);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'idUsuario' => (int)$row['id']
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
