<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = isset($_POST["idUsuario"]) ? intval($_POST["idUsuario"]) : 0;
    $nombre = $_POST["nombre"] ?? '';
    $apellidos = $_POST["apellidos"] ?? '';
    $numero = $_POST["numero"] ?? '';
    $vinculo = $_POST["vinculo"] ?? '';
    $imagenPath = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {

        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['imagen']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
            $imagenPath = $targetPath;
        } else {
            echo json_encode(["status" => "error", "message" => "Error al mover la imagen"]);
            exit();
        }
    }
    if ($idUsuario <= 0 || empty($nombre) || empty($apellidos) || empty($numero)) {
        $error_msg = "Campos faltantes:";
        $error_msg .= ($idUsuario <= 0) ? " idUsuario=$idUsuario" : "";
        $error_msg .= (empty($nombre)) ? " nombre='$nombre'" : "";
        $error_msg .= (empty($apellidos)) ? " apellidos='$apellidos'" : "";
        $error_msg .= (empty($numero)) ? " numero='$numero'" : "";
        echo json_encode(["status" => "error", "message" => $error_msg]);
        exit();
    }

    $check = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
    $check->bind_param("i", $idUsuario);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Usuario no válido (ID: $idUsuario)"]);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO contactos_confianza (idUsuario, nombre, apellidos, numero, vinculo, imagen) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $idUsuario, $nombre, $apellidos, $numero, $vinculo, $imagenPath);

    if ($stmt->execute()) {
        $idContacto = $stmt->insert_id;
        echo json_encode([
            "status" => "success",
            "message" => "Contacto guardado",
            "idContacto" => $idContacto,
            "imagenPath" => $imagenPath
        ]);
    } else {
        $error = $conn->error;
        echo json_encode(["status" => "error", "message" => "Error SQL: $error"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}
?>