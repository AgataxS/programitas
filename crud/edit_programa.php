<?php
include('../includes/header.php');
include('../db_connection.php');

// Verificar si existe el ID del programa
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Obtener el programa para editar
    $sql = "SELECT * FROM programas WHERE nombre_unico = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $programa = $stmt->fetch();

    // Verificar si se envió el formulario de edición
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_programa'])) {
        $nombre = $_POST['nombre_unico'];
        $descripcion = $_POST['descripcion'];

        // Actualizar programa en la base de datos
        $sql = "UPDATE programas SET nombre_unico = :nombre_unico, descripcion = :descripcion WHERE nombre_unico = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nombre_unico' => $nombre, 'descripcion' => $descripcion, 'id' => $id]);

        // Redirigir después de la actualización
        header("Location: programas.php");
    }
}
?>

<h1>Editar Programa</h1>

<!-- Formulario de edición -->
<div class="form-container">
    <form method="POST" class="program-form">
        <label for="nombre_unico">Nombre único:</label>
        <input type="text" name="nombre_unico" value="<?= htmlspecialchars($programa['nombre_unico']); ?>" required class="input-field">
        
        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" required class="textarea-field"><?= htmlspecialchars($programa['descripcion']); ?></textarea>
        
        <button type="submit" name="update_programa" class="submit-button">Actualizar Programa</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
