<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = isset($_POST["idUsuario"]) ? intval($_POST["idUsuario"]) : 0;

    if ($idUsuario <= 0) {
        echo json_encode(["status" => "error", "message" => "ID de usuario inválido"]);
        exit();
    }
    $check = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
    $check->bind_param("i", $idUsuario);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Usuario no válido"]);
        exit();
    }
    $stmt = $conn->prepare("SELECT COUNT(*) as cantidad FROM contactos_confianza WHERE idUsuario = ?");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cantidad = $row['cantidad'];

    echo json_encode([
        "status" => "success",
        "cantidad" => (int)$cantidad
    ]);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}
?>