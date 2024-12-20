<?php
include('../includes/header.php');
include('../db_connection.php');

// Determinar la acción
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Crear categoría
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'create') {
    $nombre = $_POST['nombre_unico'];
    $descripcion = $_POST['descripcion'];
    $categoria_padre = empty($_POST['categoria_padre_id']) ? null : $_POST['categoria_padre_id'];
    $imagen = null;

    // Manejo de imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "../uploads/categorias/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imagen = $target_dir . uniqid() . '_' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen);
    }

    $sql = "INSERT INTO categorias (nombre_unico, descripcion, imagen, categoria_padre_id) 
            VALUES (:nombre_unico, :descripcion, :imagen, :categoria_padre_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nombre_unico' => $nombre,
        'descripcion' => $descripcion,
        'imagen' => $imagen,
        'categoria_padre_id' => $categoria_padre
    ]);

    header("Location: categorias.php");
    exit();
}

// Editar categoría
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $id = $_POST['id'];
    $descripcion = $_POST['descripcion'];
    $categoria_padre = empty($_POST['categoria_padre_id']) ? null : $_POST['categoria_padre_id'];
    $imagen = $_POST['imagen_actual'];

    // Manejo de nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "../uploads/categorias/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imagen = $target_dir . uniqid() . '_' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen);
    }

    $sql = "UPDATE categorias 
            SET descripcion = :descripcion, 
                imagen = :imagen, 
                categoria_padre_id = :categoria_padre_id 
            WHERE nombre_unico = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'descripcion' => $descripcion,
        'imagen' => $imagen,
        'categoria_padre_id' => $categoria_padre,
        'id' => $id
    ]);

    header("Location: categorias.php");
    exit();
}

// Eliminar categoría
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM categorias WHERE nombre_unico = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    header("Location: categorias.php");
    exit();
}

// Obtener categorías
$sql = "SELECT c.*, cp.descripcion as categoria_padre 
        FROM categorias c 
        LEFT JOIN categorias cp ON c.categoria_padre_id = cp.nombre_unico";
$stmt = $pdo->query($sql);
$categorias = $stmt->fetchAll();

// Obtener categorías para el select de padre
$sql_padres = "SELECT nombre_unico, descripcion FROM categorias";
$stmt_padres = $pdo->query($sql_padres);
$categorias_padres = $stmt_padres->fetchAll();
?>

<!-- Mostrar lista de categorías -->
<?php if ($action == 'list'): ?>
    <h1>Gestionar Categorías</h1>
    <a href="categorias.php?action=create" class="submit-button">Nueva Categoría</a>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Imagen</th>
                <th>Categoría Padre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?= htmlspecialchars($categoria['nombre_unico']) ?></td>
                    <td><?= htmlspecialchars($categoria['descripcion']) ?></td>
                    <td>
                        <?php if ($categoria['imagen']): ?>
                            <img src="<?= htmlspecialchars($categoria['imagen']) ?>" alt="Imagen categoría" style="max-width: 50px;">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($categoria['categoria_padre'] ?? 'Sin categoría padre') ?></td>
                    <td>
                        <a href="categorias.php?action=edit&id=<?= $categoria['nombre_unico'] ?>" class="edit-button">Editar</a>
                        <a href="categorias.php?action=delete&id=<?= $categoria['nombre_unico'] ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Formulario para crear o editar -->
<?php if ($action == 'create' || $action == 'edit'): ?>
    <?php
    $categoria = ['nombre_unico' => '', 'descripcion' => '', 'imagen' => '', 'categoria_padre_id' => ''];
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM categorias WHERE nombre_unico = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $categoria = $stmt->fetch();
    }
    ?>
    <h1><?= $action == 'create' ? 'Crear Categoría' : 'Editar Categoría' ?></h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['nombre_unico']) ?>">
        <label for="nombre_unico">Nombre único:</label>
        <input type="text" name="nombre_unico" value="<?= htmlspecialchars($categoria['nombre_unico']) ?>" <?= $action == 'edit' ? 'disabled' : '' ?> required>
        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" required><?= htmlspecialchars($categoria['descripcion']) ?></textarea>
        <label for="imagen">Imagen:</label>
        <?php if ($categoria['imagen']): ?>
            <img src="<?= htmlspecialchars($categoria['imagen']) ?>" alt="Imagen actual" style="max-width: 200px;">
            <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($categoria['imagen']) ?>">
        <?php endif; ?>
        <input type="file" name="imagen" accept="image/*">
        <label for="categoria_padre_id">Categoría Padre:</label>
        <select name="categoria_padre_id">
            <option value="">Sin categoría padre</option>
            <?php foreach ($categorias_padres as $cat): ?>
                <option value="<?= htmlspecialchars($cat['nombre_unico']) ?>" <?= $cat['nombre_unico'] == $categoria['categoria_padre_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="submit-button"><?= $action == 'create' ? 'Crear' : 'Actualizar' ?></button>
    </form>
<?php endif; ?>

<?php include('../includes/footer.php'); ?>