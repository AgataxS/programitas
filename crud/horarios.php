<?php
include('../includes/header.php');
include('../db_connection.php');

// Determinar la acción
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Crear horario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'create') {
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $programa_id = $_POST['programa_id'];

    $sql = "INSERT INTO horarios (hora_inicio, hora_fin, programa_id) VALUES (:hora_inicio, :hora_fin, :programa_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['hora_inicio' => $hora_inicio, 'hora_fin' => $hora_fin, 'programa_id' => $programa_id]);

    header("Location: horarios.php");
    exit();
}

// Editar horario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $id = $_POST['id'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $programa_id = $_POST['programa_id'];

    $sql = "UPDATE horarios SET hora_inicio = :hora_inicio, hora_fin = :hora_fin, programa_id = :programa_id WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['hora_inicio' => $hora_inicio, 'hora_fin' => $hora_fin, 'programa_id' => $programa_id, 'id' => $id]);

    header("Location: horarios.php");
    exit();
}

// Eliminar horario
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM horarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    header("Location: horarios.php");
    exit();
}

// Obtener horarios
$sql = "SELECT h.*, p.descripcion AS programa_nombre 
        FROM horarios h
        LEFT JOIN programas p ON h.programa_id = p.nombre_unico";
$stmt = $pdo->query($sql);
$horarios = $stmt->fetchAll();

// Obtener programas para el select
$sql_programas = "SELECT nombre_unico, descripcion FROM programas";
$stmt_programas = $pdo->query($sql_programas);
$programas = $stmt_programas->fetchAll();
?>

<!-- Mostrar lista de horarios -->
<?php if ($action == 'list'): ?>
    <h1>Gestionar Horarios</h1>
    <a href="horarios.php?action=create" class="submit-button">Nuevo Horario</a>
    <table>
        <thead>
            <tr>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Programa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($horarios as $horario): ?>
                <tr>
                    <td><?= htmlspecialchars($horario['hora_inicio']) ?></td>
                    <td><?= htmlspecialchars($horario['hora_fin']) ?></td>
                    <td><?= htmlspecialchars($horario['programa_nombre']) ?></td>
                    <td>
                        <a href="horarios.php?action=edit&id=<?= $horario['id'] ?>" class="edit-button">Editar</a>
                        <a href="horarios.php?action=delete&id=<?= $horario['id'] ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este horario?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Formulario para crear o editar -->
<?php if ($action == 'create' || $action == 'edit'): ?>
    <?php
    $horario = ['id' => '', 'hora_inicio' => '', 'hora_fin' => '', 'programa_id' => ''];
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM horarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $horario = $stmt->fetch();
    }
    ?>
    <h1><?= $action == 'create' ? 'Crear Horario' : 'Editar Horario' ?></h1>
    <form method="POST">
        <!-- Asegurarse de que siempre haya un valor para 'id' -->
        <input type="hidden" name="id" value="<?= htmlspecialchars($horario['id']) ?>">
        <label for="hora_inicio">Hora Inicio:</label>
        <input type="time" name="hora_inicio" value="<?= htmlspecialchars($horario['hora_inicio']) ?>" required>
        <label for="hora_fin">Hora Fin:</label>
        <input type="time" name="hora_fin" value="<?= htmlspecialchars($horario['hora_fin']) ?>" required>
        <label for="programa_id">Programa:</label>
        <select name="programa_id" required>
            <?php foreach ($programas as $programa): ?>
                <option value="<?= htmlspecialchars($programa['nombre_unico']) ?>" <?= $programa['nombre_unico'] == $horario['programa_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($programa['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="submit-button"><?= $action == 'create' ? 'Crear' : 'Actualizar' ?></button>
    </form>
<?php endif; ?>

<?php include('../includes/footer.php'); ?>
