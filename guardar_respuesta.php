<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : 0;
    $fase = $_POST["fase"] ?? '';
    $pregunta = isset($_POST["pregunta"]) ? intval($_POST["pregunta"]) : 0;
    $respuesta = $_POST["respuesta"] ?? '';
    $valor = isset($_POST["valor"]) ? intval($_POST["valor"]) : 0;

    if ($idUsuario <= 0 || empty($fase) || $pregunta <= 0 || empty($respuesta)) {
        echo json_encode([
            "status" => "error",
            "message" => "Campos faltantes"
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
    $stmt = $conn->prepare("INSERT INTO respuestas_autoevaluacion (idUsuario, fase, pregunta, respuesta, valor) 
                            VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE respuesta = VALUES(respuesta), valor = VALUES(valor)");
    $stmt->bind_param("isiss", $idUsuario, $fase, $pregunta, $respuesta, $valor);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Respuesta guardada"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error SQL: " . $conn->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}
?>