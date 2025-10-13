<?php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);
include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $apellidos = $_POST["apellidos"];
    $numero = $_POST["numero"];
    $correo = $_POST["correo"];
    $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? LIMIT 1");
    $check->bind_param("s", $correo);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(["status"=>"exists","idUsuario"=>$row["id"]]);
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, numero, correo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $apellidos, $numero, $correo);

        if ($stmt->execute()) {
        $idUsuario = $stmt->insert_id;
        echo json_encode([
            "status" => "success",
            "idUsuario" => (int)$idUsuario
        ]);
    }

        $stmt->close();
    }

    $check->close();
    $conn->close();
} else {
    echo json_encode(["status"=>"invalid_request"]);
}
?>
