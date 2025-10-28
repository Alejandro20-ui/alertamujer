<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Manejo de preflight OPTIONS (para CORS)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "invalid_method"]);
    exit();
}

include "conexion.php";

// Validación básica de campos
$required = ['nombre', 'apellidos', 'numero', 'correo'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        echo json_encode(["status" => "missing_field", "field" => $field]);
        $conn->close();
        exit();
    }
}

$nombre = trim($_POST["nombre"]);
$apellidos = trim($_POST["apellidos"]);
$numero = trim($_POST["numero"]);
$correo = trim($_POST["correo"]);

// Validación simple de correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "invalid_email"]);
    $conn->close();
    exit();
}

// Verificar si el correo ya existe
$check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? LIMIT 1");
$check->bind_param("s", $correo);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["status" => "exists", "idUsuario" => (int)$row["id"]]);
} else {
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, numero, correo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $apellidos, $numero, $correo);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "idUsuario" => (int)$stmt->insert_id
        ]);
    } else {
        error_log("Registro fallido: " . $stmt->error);
        echo json_encode(["status" => "insert_error"]);
    }
    $stmt->close();
}

$check->close();
$conn->close();
?>