<?php
// index.php - Solo para que Railway reconozca el proyecto PHP
http_response_code(200);
echo json_encode(["status" => "API activa", "app" => "Alerta Mujer"]);
?>