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

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$ip = getClientIP();
$payload = json_encode(['nombre'=>$nombre,'apellidos'=>$apellidos,'numero'=>$numero]);

// ------------- DETECCIÓN DE PATRONES SOSPECHOSOS -------------
// Ajusta el patrón si quieres mayor o menor sensibilidad.
// Lo que detectamos aquí: tokens típicos de SQL, caracteres peligrosos, comentarios, punto y coma, etc.
$sql_keywords_pattern = '/(\b(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|SHOW|REPLACE|EXEC|EXECUTE|DECLARE|CAST|CONVERT)\b)/i';
$danger_chars_pattern = '/(--|;|\/\*|\*\/|@@|char\(|nchar\(|varchar\(|\bOR\b\s+\d+=\d+)/i';

$is_suspicious = false;
if (preg_match($sql_keywords_pattern, $payload) || preg_match($danger_chars_pattern, $payload)) {
    $is_suspicious = true;
}

if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/u', $nombre)) $is_suspicious = true;
if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/u', $apellidos)) $is_suspicious = true;
if (!preg_match('/^[0-9]{9}$/', $numero)) $is_suspicious = true;

if ($is_suspicious) {
    insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload, 1, 'detenido');
    echo json_encode(['status' => 'error', 'message' => 'Entrada inválida detectada.']);
    $conn->close();
    exit();
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ? AND apellidos = ? AND numero = ? LIMIT 1");
if (!$stmt) {
    insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload . " -- prepare_failed: " . $conn->error, 0, 'peligro');
    echo json_encode(['status' => 'error', 'message' => 'Error interno.']);
    $conn->close();
    exit();
}

$stmt->bind_param("sss", $nombre, $apellidos, $numero);
$execOk = $stmt->execute();
if (!$execOk) {
    insert_intrusion_log($conn, $nombre, $apellidos, $numero, $ip, $payload . " -- execute_failed: " . $stmt->error, 0, 'peligro');
    echo json_encode(['status' => 'error', 'message' => 'Error interno.']);
    $stmt->close();
    $conn->close();
    exit();
}

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
exit();
