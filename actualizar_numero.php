<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = isset($_POST["idUsuario"]) ? intval($_POST["idUsuario"]) : 0;
    $idContacto = isset($_POST["idContacto"]) ? intval($_POST["idContacto"]) : 0;
    $numero = $_POST["numero"] ?? '';

    if ($idUsuario <= 0 || $idContacto <= 0 || empty($numero)) {
        echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
        exit();
    }

    $check = $conn->prepare("SELECT id FROM contactos_confianza WHERE id = ? AND idUsuario = ?");
    $check->bind_param("ii", $idContacto, $idUsuario);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Contacto no válido"]);
        exit();
    }

    if (!preg_match('/^\d{9}$/', $numero)) {
        echo json_encode(["status" => "error", "message" => "Número debe tener 9 dígitos"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE contactos_confianza SET numero = ? WHERE id = ?");
    $stmt->bind_param("si", $numero, $idContacto);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Número actualizado"
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error SQL: " . $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}
?>