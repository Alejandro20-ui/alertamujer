<?php
$host = $_ENV['MYSQLHOST'] ?? 'localhost';
$user = $_ENV['MYSQLUSER'] ?? 'root';
$pass = $_ENV['MYSQLPASSWORD'] ?? '';
$db   = $_ENV['MYSQLDATABASE'] ?? 'alertamujer';
$port = $_ENV['MYSQLPORT'] ?? 3306;

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$numero = $_POST['numero'];
$usuario_id = $_POST['usuario'];

$sql = "INSERT INTO llamadas (numero, usuario_id, fecha_hora)
        VALUES ('$numero', '$usuario_id', NOW())";

if ($conn->query($sql) === TRUE) {
    echo "Llamada registrada con éxito";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
