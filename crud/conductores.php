<?php
include('../includes/header.php');
include('../db_connection.php');

// Determinar la acción
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Crear conductor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'create') {
    $nombre = $_POST['nombre'];
    $programa_id = $_POST['programa_id'];

    $sql = "INSERT INTO conductores (nombre, programa_id) VALUES (:nombre, :programa_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nombre' => $nombre, 'programa_id' => $programa_id]);

    header("Location: conductores.php");
    exit();
}

// Editar conductor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $programa_id = $_POST['programa_id'];

    $sql = "UPDATE conductores SET nombre = :nombre, programa_id = :programa_id WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nombre' => $nombre, 'programa_id' => $programa_id, 'id' => $id]);

    header("Location: conductores.php");
    exit();
}

// Eliminar conductor
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM conductores WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    header("Location: conductores.php");
    exit();
}

// Obtener conductores
$sql = "SELECT c.*, p.descripcion AS programa_nombre 
        FROM conductores c
        LEFT JOIN programas p ON c.programa_id = p.nombre_unico";
$stmt = $pdo->query($sql);
$conductores = $stmt->fetchAll();

// Obtener programas para el select
$sql_programas = "SELECT nombre_unico, descripcion FROM programas";
$stmt_programas = $pdo->query($sql_programas);
$programas = $stmt_programas->fetchAll();
?>

<!-- Mostrar lista de conductores -->
<?php if ($action == 'list'): ?>
    <h1>Gestionar Conductores</h1>
    <a href="conductores.php?action=create" class="submit-button">Nuevo Conductor</a>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Programa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($conductores as $conductor): ?>
                <tr>
                    <td><?= htmlspecialchars($conductor['nombre']) ?></td>
                    <td><?= htmlspecialchars($conductor['programa_nombre']) ?></td>
                    <td>
                        <a href="conductores.php?action=edit&id=<?= $conductor['id'] ?>" class="edit-button">Editar</a>
                        <a href="conductores.php?action=delete&id=<?= $conductor['id'] ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este conductor?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Formulario para crear o editar -->
<?php if ($action == 'create' || $action == 'edit'): ?>
    <?php
    // Inicializar variables para crear o editar
    $conductor = ['nombre' => '', 'programa_id' => ''];
    
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM conductores WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $conductor = $stmt->fetch();

        // Verificar si se encontró el conductor
        if (!$conductor) {
            echo "No se encontró el conductor con el ID: $id";
            exit;
        }
    }
    ?>

    <h1><?= $action == 'create' ? 'Crear Conductor' : 'Editar Conductor' ?></h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($conductor['id'] ?? '') ?>">
        
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($conductor['nombre']) ?>" required>
        
        <label for="programa_id">Programa:</label>
        <select name="programa_id" required>
            <?php foreach ($programas as $programa): ?>
                <option value="<?= htmlspecialchars($programa['nombre_unico']) ?>" <?= $programa['nombre_unico'] == $conductor['programa_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($programa['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" class="submit-button"><?= $action == 'create' ? 'Crear' : 'Actualizar' ?></button>
    </form>
<?php endif; ?>

<?php include('../includes/footer.php'); ?>
