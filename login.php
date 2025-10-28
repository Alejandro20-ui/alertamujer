<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(0);

// Configuración de base de datos
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

// Lee los datos
$nombre = '';
$apellidos = '';
$numero = '';
$correo = '';

$contentType = $_SERVER["CONTENT_TYPE"] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if ($data) {
        $nombre = trim($data['nombre'] ?? '');
        $apellidos = trim($data['apellidos'] ?? '');
        $numero = trim($data['numero'] ?? '');
        $correo = trim($data['correo'] ?? '');
    }
} else {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
}

// Validación de campos vacíos
if (empty($nombre) || empty($apellidos) || empty($numero) || empty($correo)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
    $conn->close();
    exit();
}

// Validaciones
if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑüÜ\s\'-]{1,100}$/u', $nombre)) {
    echo json_encode(['status' => 'error', 'message' => 'El nombre contiene caracteres inválidos.']);
    $conn->close();
    exit();
}

if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑüÜ\s\'-]{1,100}$/u', $apellidos)) {
    echo json_encode(['status' => 'error', 'message' => 'Los apellidos contienen caracteres inválidos.']);
    $conn->close();
    exit();
}

if (!preg_match('/^[0-9]{9,15}$/', $numero)) {
    echo json_encode(['status' => 'error', 'message' => 'El número debe tener entre 9 y 15 dígitos.']);
    $conn->close();
    exit();
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'El correo electrónico no es válido.']);
    $conn->close();
    exit();
}

// Verifica si el usuario ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE numero = ? OR correo = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor.']);
    $conn->close();
    exit();
}

$stmt->bind_param("ss", $numero, $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Usuario ya existe
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'exists',
        'message' => 'El usuario ya está registrado.',
        'idUsuario' => (int)$row['id']
    ]);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Inserta nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, numero, correo) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta.']);
    $conn->close();
    exit();
}

$stmt->bind_param("ssss", $nombre, $apellidos, $numero, $correo);

if ($stmt->execute()) {
    $idUsuario = $conn->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Registro exitoso.',
        'idUsuario' => $idUsuario
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al registrar usuario: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
exit();
?>