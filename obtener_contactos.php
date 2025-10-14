<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = isset($_POST["idUsuario"]) ? intval($_POST["idUsuario"]) : 0;

    if ($idUsuario <= 0) {
        echo json_encode(["status" =>"error", "message" => "ID de usuario inválido"]);
        exit();
    }

    $check = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
    $check->bind_param("i", $idUsuario);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Usuario no válido"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, nombre, apellidos, numero, vinculo, imagen FROM contactos_confianza WHERE idUsuario = ? ORDER BY id LIMIT 3");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $contactos = [];
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['imagen'])) {
            $rutaLocal = __DIR__ . '/' . $row['imagen'];
            if (file_exists($rutaLocal)) {
        $row['imagen'] = "https://warmi360-production.up.railway.app/" . $row['imagen'];
        } else {
            $row['imagen'] = null;
        }
        }
        $contactos[] = $row;
    }

    if (count($contactos) > 0) {
        echo json_encode(["status" => "success", "data" => $contactos]);
    } else {
        echo json_encode(["status" => "empty", "data" => []]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}
?>