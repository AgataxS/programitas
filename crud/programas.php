<?php
include('../includes/header.php');
include('../db_connection.php');

// Crear un programa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_programa'])) {
    $nombre = $_POST['nombre_unico'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO programas (nombre_unico, descripcion) VALUES (:nombre_unico, :descripcion)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nombre_unico' => $nombre, 'descripcion' => $descripcion]);
}

// Obtener los programas
$sql = "SELECT * FROM programas";
$stmt = $pdo->query($sql);
$programas = $stmt->fetchAll();
?>

<h1>Gestionar Programas</h1>

<!-- Formulario para agregar un nuevo programa -->
<div class="form-container">
    <form method="POST" class="program-form">
        <label for="nombre_unico">Nombre único:</label>
        <input type="text" name="nombre_unico" required class="input-field">
        
        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" required class="textarea-field"></textarea>
        
        <button type="submit" name="create_programa" class="submit-button">Crear Programa</button>
    </form>
</div>

<!-- Lista de programas -->
<div class="program-list">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($programas as $programa): ?>
                <tr>
                    <td><?= htmlspecialchars($programa['nombre_unico']); ?></td>
                    <td><?= htmlspecialchars($programa['descripcion']); ?></td>
                    <td>
                        <a href="edit_programa.php?id=<?= $programa['nombre_unico']; ?>" class="edit-button">Editar</a>
                        <a href="delete_programa.php?id=<?= $programa['nombre_unico']; ?>" class="delete-button">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>
