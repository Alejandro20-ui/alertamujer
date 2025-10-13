<?php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0);
include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = $_POST["idUsuario"];
    $codigo = $_POST["codigo"];
    $check = $conn->prepare("SELECT id FROM otp_codes WHERE idUsuario = ? AND codigo = ? AND expiracion > NOW() ORDER BY id DESC LIMIT 1");
    $check->bind_param("is", $idUsuario, $codigo);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "invalid_or_expired"]);
    }

    $check->close();
    $conn->close();
} else {
    echo json_encode(["status" => "invalid_request"]);
}
?>
