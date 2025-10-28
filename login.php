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

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload, $detected, $status) {
    $stmt = $conn->prepare("INSERT INTO intrusion_logs (nombre, apellidos, numero, ip, payload, detected, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) return false;
    $det = $detected ? 1 : 0;
    $stmt->bind_param("sssssis", $nombre, $apellidos, $numero, $ip, $payload, $det, $status);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Lee los datos - soporta tanto POST tradicional como JSON
$nombre = '';
$apellidos = '';
$numero = '';

// Verifica si viene como JSON
$contentType = $_SERVER["CONTENT_TYPE"] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if ($data) {
        $nombre = trim($data['nombre'] ?? '');
        $apellidos = trim($data['apellidos'] ?? '');
        $numero = trim($data['numero'] ?? '');
    }
} else {
    // POST tradicional
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
}

$ip = getClientIP();
$payload = json_encode(['nombre'=>$nombre,'apellidos'=>$apellidos,'numero'=>$numero]);

// Validación básica de campos vacíos
if (empty($nombre) || empty($apellidos) || empty($numero)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
    $conn->close();
    exit();
}

// ------------- DETECCIÓN DE PATRONES SOSPECHOSOS (MEJORADA) -------------
$is_suspicious = false;

// Patrones SQL más específicos para evitar falsos positivos
$sql_keywords_pattern = '/(\b(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|EXEC|EXECUTE)\b.*\b(FROM|WHERE|INTO|TABLE)\b)/i';
$danger_chars_pattern = '/(--|;.*SELECT|\/\*.*\*\/|@@|0x[0-9a-f]+)/i';

// Solo marca como sospechoso si hay patrones SQL realmente peligrosos
if (preg_match($sql_keywords_pattern, $payload) || preg_match($danger_chars_pattern, $payload)) {
    $is_suspicious = true;
}

// Validación de nombre: permite letras, espacios y acentos (MEJORADA - más permisiva)
if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑüÜ\s\'-]{1,100}$/u', $nombre)) {
    $is_suspicious = true;
}

// Validación de apellidos: permite letras, espacios y acentos (MEJORADA - más permisiva)
if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑüÜ\s\'-]{1,100}$/u', $apellidos)) {
    $is_suspicious = true;
}

// Validación de número: 9 dígitos (puedes ajustar según tu país)
if (!preg_match('/^[0-9]{9,15}$/', $numero)) {
    $is_suspicious = true;
}

// Si es sospechoso, registra y bloquea
if ($is_suspicious) {
    insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload, 1, 'detenido');
    echo json_encode(['status' => 'error', 'message' => 'Entrada inválida detectada.']);
    $conn->close();
    exit();
}

// Consulta preparada para prevenir SQL injection
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ? AND apellidos = ? AND numero = ? LIMIT 1");
if (!$stmt) {
    insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload . " -- prepare_failed: " . $conn->error, 0, 'peligro');
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor.']);
    $conn->close();
    exit();
}

$stmt->bind_param("sss", $nombre, $apellidos, $numero);
$execOk = $stmt->execute();

if (!$execOk) {
    insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload . " -- execute_failed: " . $stmt->error, 0, 'peligro');
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor.']);
    $stmt->close();
    $conn->close();
    exit();
}

$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Login exitoso - registra como acceso legítimo
    insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload, 0, 'exitoso');
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Login exitoso',
        'idUsuario' => (int)$row['id']
    ]);
} else {
    // Credenciales incorrectas
    insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload, 0, 'fallido');
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Credenciales incorrectas. Verifica tus datos.'
    ]);
}

$stmt->close();
$conn->close();
exit();
?>