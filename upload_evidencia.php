<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = getenv('MYSQLHOST') ?: 'maglev.proxy.rlwy.net';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: 'CZhVEBZHQRoZvxHsUoPlOrWgSTXnacGc';
$port = getenv('MYSQLPORT') ?: '50204';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    if (empty($_POST) && empty($_FILES)) {
    throw new Exception('La solicitud está vacía. ¿El archivo es demasiado grande?');
}

    if (!isset($_POST['user_id']) || !isset($_POST['tipo']) || !isset($_FILES['archivo'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $user_id = intval($_POST['user_id']);
    $tipo = $_POST['tipo'];

    if (!in_array($tipo, ['foto', 'video'])) {
        throw new Exception('Tipo no válido');
    }

    $archivo = $_FILES['archivo'];
    $errorMessages = [
    UPLOAD_ERR_OK => 'Sin error',
    UPLOAD_ERR_INI_SIZE => 'El archivo excede upload_max_filesize',
    UPLOAD_ERR_FORM_SIZE => 'El archivo excede MAX_FILE_SIZE en el formulario',
    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
    UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
    UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
    UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
    UPLOAD_ERR_EXTENSION => 'Extensión PHP detuvo la subida',
];

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir el archivo: ' . $archivo['error']);
    }

    $contenido = file_get_contents($archivo['tmp_name']);
    $tamano = $archivo['size'];
    $nombre_original = $archivo['name'];

    if ($tamano > 50 * 1024 * 1024) {
        throw new Exception('El archivo es demasiado grande (máximo 50MB)');
    }

    $sql = "INSERT INTO evidencias (user_id, tipo, archivo, nombre_archivo, tamano_bytes)
            VALUES (:user_id, :tipo, :archivo, :nombre_archivo, :tamano)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->bindParam(':archivo', $contenido, PDO::PARAM_LOB);
    $stmt->bindParam(':nombre_archivo', $nombre_original, PDO::PARAM_STR);
    $stmt->bindParam(':tamano', $tamano, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $evidencia_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Evidencia guardada correctamente',
            'evidencia_id' => $evidencia_id,
            'tipo' => $tipo,
            'tamano' => $tamano
        ]);
    } else {
        throw new Exception('Error al guardar en la base de datos');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>