<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Manejo de preflight OPTIONS (CORS)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "invalid_method"]);
    exit();
}

include "conexion.php";

$required = ['nombre', 'apellidos', 'numero'];
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

// Buscar usuario en base de datos
$stmt = $conn->prepare("SELECT id, nombre, apellidos, numero FROM usuarios WHERE nombre = ? AND apellidos = ? AND numero = ? LIMIT 1");
$stmt->bind_param("sss", $nombre, $apellidos, $numero);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "idUsuario" => (int)$row["id"],
        "nombre" => $row["nombre"]
    ]);
} else {
    echo json_encode(["status" => "not_found"]);
}

$stmt->close();
$conn->close();
?>
