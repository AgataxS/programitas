<?php
// Conexión a la base de datos PostgreSQL
$host = 'localhost';
$port = '5432';
$dbname = 'psf';
$user = 'postgres';
$password = '12341234';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>
