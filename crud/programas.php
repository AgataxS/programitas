<?php
include('../includes/header.php');
include('../db_connection.php');

// Determinar la acción
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Crear programa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'create') {
    $nombre = $_POST['nombre_unico'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO programas (nombre_unico, descripcion) VALUES (:nombre_unico, :descripcion)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nombre_unico' => $nombre, 'descripcion' => $descripcion]);

    header("Location: programas.php");
    exit();
}

// Editar programa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre_unico'];
    $descripcion = $_POST['descripcion'];

    $sql = "UPDATE programas SET nombre_unico = :nombre_unico, descripcion = :descripcion WHERE nombre_unico = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nombre_unico' => $nombre, 'descripcion' => $descripcion, 'id' => $id]);

    header("Location: programas.php");
    exit();
}

// Eliminar programa
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM programas WHERE nombre_unico = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    header("Location: programas.php");
    exit();
}

// Obtener programas
$sql = "SELECT * FROM programas";
$stmt = $pdo->query($sql);
$programas = $stmt->fetchAll();
?>

<!-- Mostrar lista de programas -->
<?php if ($action == 'list'): ?>
    <h1>Gestionar Programas</h1>
    <a href="programas.php?action=create" class="submit-button">Nuevo Programa</a>
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
                    <td><?= htmlspecialchars($programa['nombre_unico']) ?></td>
                    <td><?= htmlspecialchars($programa['descripcion']) ?></td>
                    <td>
                        <a href="programas.php?action=edit&id=<?= $programa['nombre_unico'] ?>" class="edit-button">Editar</a>
                        <a href="programas.php?action=delete&id=<?= $programa['nombre_unico'] ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este programa?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Formulario para crear o editar -->
<?php if ($action == 'create' || $action == 'edit'): ?>
    <?php
    $programa = ['nombre_unico' => '', 'descripcion' => ''];
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM programas WHERE nombre_unico = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $programa = $stmt->fetch();
    }
    ?>
    <h1><?= $action == 'create' ? 'Crear Programa' : 'Editar Programa' ?></h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($programa['nombre_unico']) ?>">
        <label for="nombre_unico">Nombre único:</label>
        <input type="text" name="nombre_unico" value="<?= htmlspecialchars($programa['nombre_unico']) ?>" required>
        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" required><?= htmlspecialchars($programa['descripcion']) ?></textarea>
        <button type="submit" class="submit-button"><?= $action == 'create' ? 'Crear' : 'Actualizar' ?></button>
    </form>
<?php endif; ?>

<?php include('../includes/footer.php'); ?>