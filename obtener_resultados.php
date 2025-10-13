<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Accept");

include "conexion.php";

$method = $_SERVER["REQUEST_METHOD"];
$idUsuario = 0;

if ($method == "POST") {
    $idUsuario = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : 0;
} elseif ($method == "GET") {
    $idUsuario = isset($_GET["id_usuario"]) ? intval($_GET["id_usuario"]) : 0;
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido"
    ]);
    exit();
}

if ($idUsuario <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "ID de usuario requerido"
    ]);
    exit();
}

$check = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
$check->bind_param("i", $idUsuario);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => "error", "message" => "Usuario no válido"]);
    exit();
}

$fases = ["fisica", "economica", "psicologica"];
$resultados = [];

foreach ($fases as $fase) {
    $stmt = $conn->prepare("SELECT SUM(valor) as total FROM respuestas_autoevaluacion WHERE idUsuario = ? AND fase = ?");
    $stmt->bind_param("is", $idUsuario, $fase);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $resultados[$fase] = $resultado['total'] ?? 0;
}

echo json_encode([
    "status" => "success",
    "fisica" => (int)$resultados["fisica"],
    "economica" => (int)$resultados["economica"],
    "psicologica" => (int)$resultados["psicologica"]
], JSON_NUMERIC_CHECK);;

$conn->close();
?>