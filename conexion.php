<?php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST') ?: 'NO_HOST';
$user = getenv('MYSQLUSER') ?: 'NO_USER';
$pass = getenv('MYSQLPASSWORD') ?: 'NO_PASS';
$db   = getenv('MYSQLDATABASE') ?: 'NO_DB';
$port = getenv('MYSQLPORT') ?: 'NO_PORT';

echo json_encode([
    "MYSQLHOST" => $host,
    "MYSQLUSER" => $user,
    "MYSQLDATABASE" => $db,
    "MYSQLPORT" => $port
]);
