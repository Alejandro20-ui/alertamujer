<?php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);
include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = $_POST["idUsuario"];
    $numero = $_POST["numero"];
    $check = $conn->prepare("SELECT id, nombre FROM usuarios WHERE id = ? AND numero = ? LIMIT 1");
    $check->bind_param("is", $idUsuario, $numero);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $codigo = rand(100000, 999999);
        $expiracion = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $insert = $conn->prepare("INSERT INTO otp_codes (idUsuario, codigo, expiracion) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $idUsuario, $codigo, $expiracion);
        $insert->execute();
        $insert->close();

        echo json_encode([
            "status" => "success",
            "idUsuario" => $row["id"],
            "nombre" => $row["nombre"],
            "otp" => $codigo, // 🔴 en producción NO devuelvas esto, solo para pruebas
            "expira" => $expiracion
        ]);
    } else {
        echo json_encode(["status" => "not_found"]);
    }

    $check->close();
    $conn->close();
} else {
    echo json_encode(["status" => "invalid_request"]);
}
?>
