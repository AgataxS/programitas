<?php
include('../includes/header.php');
include('../db_connection.php');

// Determinar la acción
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Crear comentario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'create') {
    $texto = $_POST['texto'];
    $nota_id = $_POST['nota_id'];
    $usuario_email = $_POST['usuario_email'];

    $sql = "INSERT INTO comentarios (texto, nota_id, usuario_email) VALUES (:texto, :nota_id, :usuario_email)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['texto' => $texto, 'nota_id' => $nota_id, 'usuario_email' => $usuario_email]);

    header("Location: comentarios.php");
    exit();
}

// Editar comentario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $id = $_POST['id'];
    $texto = $_POST['texto'];

    $sql = "UPDATE comentarios SET texto = :texto WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['texto' => $texto, 'id' => $id]);

    header("Location: comentarios.php");
    exit();
}

// Eliminar comentario
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM comentarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    header("Location: comentarios.php");
    exit();
}

// Obtener comentarios
$sql = "SELECT c.*, n.titulo_unico AS nota_titulo, u.username AS usuario_nombre 
        FROM comentarios c
        LEFT JOIN notas n ON c.nota_id = n.titulo_unico
        LEFT JOIN usuarios u ON c.usuario_email = u.email";
$stmt = $pdo->query($sql);
$comentarios = $stmt->fetchAll();

// Obtener notas y usuarios para los selects
$sql_notas = "SELECT titulo_unico FROM notas";
$stmt_notas = $pdo->query($sql_notas);
$notas = $stmt_notas->fetchAll();

$sql_usuarios = "SELECT email, username FROM usuarios";
$stmt_usuarios = $pdo->query($sql_usuarios);
$usuarios = $stmt_usuarios->fetchAll();
?>

<!-- Mostrar lista de comentarios -->
<?php if ($action == 'list'): ?>
    <h1>Gestionar Comentarios</h1>
    <a href="comentarios.php?action=create" class="submit-button">Nuevo Comentario</a>
    <table>
        <thead>
            <tr>
                <th>Texto</th>
                <th>Nota</th>
                <th>Usuario</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comentarios as $comentario): ?>
                <tr>
                    <td><?= htmlspecialchars($comentario['texto']) ?></td>
                    <td><?= htmlspecialchars($comentario['nota_titulo']) ?></td>
                    <td><?= htmlspecialchars($comentario['usuario_nombre']) ?></td>
                    <td>
                        <a href="comentarios.php?action=edit&id=<?= $comentario['id'] ?>" class="edit-button">Editar</a>
                        <a href="comentarios.php?action=delete&id=<?= $comentario['id'] ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este comentario?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Formulario para crear o editar -->
<?php if ($action == 'create' || $action == 'edit'): ?>
    <?php
    $comentario = ['id' => '', 'texto' => '', 'nota_id' => '', 'usuario_email' => ''];
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM comentarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $comentario = $stmt->fetch();
    }
    ?>
    <h1><?= $action == 'create' ? 'Crear Comentario' : 'Editar Comentario' ?></h1>
    <form method="POST">
        <!-- Asegúrate de que 'id' siempre esté presente -->
        <input type="hidden" name="id" value="<?= isset($comentario['id']) ? htmlspecialchars($comentario['id']) : '' ?>">
        <label for="texto">Texto:</label>
        <textarea name="texto" required><?= htmlspecialchars($comentario['texto']) ?></textarea>
        <?php if ($action == 'create'): ?>
            <label for="nota_id">Nota:</label>
            <select name="nota_id" required>
                <?php foreach ($notas as $nota): ?>
                    <option value="<?= htmlspecialchars($nota['titulo_unico']) ?>" <?= $nota['titulo_unico'] == $comentario['nota_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nota['titulo_unico']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="usuario_email">Usuario:</label>
            <select name="usuario_email" required>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= htmlspecialchars($usuario['email']) ?>" <?= $usuario['email'] == $comentario['usuario_email'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usuario['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <button type="submit" class="submit-button"><?= $action == 'create' ? 'Crear' : 'Actualizar' ?></button>
    </form>
<?php endif; ?>

<?php include('../includes/footer.php'); ?>
