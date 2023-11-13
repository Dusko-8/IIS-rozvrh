<?php
$host = 'localhost';
$db   = 'xsluka00';
$user = 'xsluka00';
$pass = 'do2bonpe';
$port = '/var/run/mysql/mysql.sock';

$dsn = "mysql:host=$host;dbname=$db;port=$port";

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo "Connection error: ".$e->getMessage();
	die();
}
?>