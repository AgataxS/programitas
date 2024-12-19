<?php
include('../db_connection.php');

// Verificar si existe el ID del programa
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Eliminar programa de la base de datos
    $sql = "DELETE FROM programas WHERE nombre_unico = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    // Redirigir a la página principal después de eliminar
    header("Location: programas.php");
}
?>
