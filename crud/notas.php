<?php
include('../includes/header.php');
include('../db_connection.php');

// Crear nota
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_nota'])) {
    $titulo = $_POST['titulo_unico'];
    $contenido = $_POST['contenido'];
    $resumen = $_POST['resumen'];
    $programa_id = $_POST['programa_id'];
    $categorias = isset($_POST['categorias']) ? $_POST['categorias'] : [];

    // Manejo de imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "../uploads/notas/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imagen = $target_dir . uniqid() . '_' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen);
    }

    try {
        $pdo->beginTransaction();

        // Insertar nota
        $sql = "INSERT INTO notas (titulo_unico, contenido, imagen, resumen, programa_id) 
                VALUES (:titulo, :contenido, :imagen, :resumen, :programa_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'titulo' => $titulo,
            'contenido' => $contenido,
            'imagen' => $imagen,
            'resumen' => $resumen,
            'programa_id' => $programa_id
        ]);

        // Insertar relaciones con categorías
        foreach ($categorias as $categoria_id) {
            $sql = "INSERT INTO notas_categorias (nota_id, categoria_id) VALUES (:nota_id, :categoria_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nota_id' => $titulo,
                'categoria_id' => $categoria_id
            ]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// Actualizar nota
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_nota'])) {
    $titulo = $_POST['titulo_unico'];
    $contenido = $_POST['contenido'];
    $resumen = $_POST['resumen'];
    $programa_id = $_POST['programa_id'];
    $categorias = isset($_POST['categorias']) ? $_POST['categorias'] : [];
    $nota_id = $_POST['nota_id'];

    // Manejo de imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "../uploads/notas/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imagen = $target_dir . uniqid() . '_' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen);
    }

    try {
        $pdo->beginTransaction();

        // Actualizar nota
        $sql = "UPDATE notas SET titulo_unico = :titulo, contenido = :contenido, imagen = :imagen, resumen = :resumen, programa_id = :programa_id WHERE titulo_unico = :nota_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'titulo' => $titulo,
            'contenido' => $contenido,
            'imagen' => $imagen,
            'resumen' => $resumen,
            'programa_id' => $programa_id,
            'nota_id' => $nota_id
        ]);

        // Eliminar relaciones antiguas
        $sql = "DELETE FROM notas_categorias WHERE nota_id = :nota_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nota_id' => $nota_id]);

        // Insertar nuevas relaciones con categorías
        foreach ($categorias as $categoria_id) {
            $sql = "INSERT INTO notas_categorias (nota_id, categoria_id) VALUES (:nota_id, :categoria_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nota_id' => $titulo,
                'categoria_id' => $categoria_id
            ]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// Eliminar nota
if (isset($_GET['delete_nota'])) {
    $nota_id = $_GET['delete_nota'];

    try {
        $pdo->beginTransaction();

        // Eliminar relaciones con categorías
        $sql = "DELETE FROM notas_categorias WHERE nota_id = :nota_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nota_id' => $nota_id]);

        // Eliminar la nota
        $sql = "DELETE FROM notas WHERE titulo_unico = :nota_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nota_id' => $nota_id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// Obtener notas con sus categorías
$sql = "SELECT n.*, p.descripcion as programa_nombre,
        STRING_AGG(c.nombre_unico, ', ') as categorias
        FROM notas n
        LEFT JOIN programas p ON n.programa_id = p.nombre_unico
        LEFT JOIN notas_categorias nc ON n.titulo_unico = nc.nota_id
        LEFT JOIN categorias c ON nc.categoria_id = c.nombre_unico
        GROUP BY n.titulo_unico, n.contenido, n.imagen, n.resumen, n.programa_id, p.descripcion";
$stmt = $pdo->query($sql);
$notas = $stmt->fetchAll();

// Obtener programas para el select
$sql_programas = "SELECT nombre_unico, descripcion FROM programas";
$stmt_programas = $pdo->query($sql_programas);
$programas = $stmt_programas->fetchAll();

// Obtener categorías para el select múltiple
$sql_categorias = "SELECT nombre_unico, descripcion FROM categorias";
$stmt_categorias = $pdo->query($sql_categorias);
$categorias = $stmt_categorias->fetchAll();
?>

<h1 class="page-title">Gestionar Notas</h1>

<!-- Formulario para agregar nueva nota -->
<div class="form-container">
    <form method="POST" enctype="multipart/form-data" class="note-form">
        <label for="titulo_unico">Título único:</label>
        <input type="text" name="titulo_unico" required class="input-field">
        
        <label for="contenido">Contenido:</label>
        <textarea name="contenido" required class="textarea-field rich-editor"></textarea>
        
        <label for="resumen">Resumen:</label>
        <textarea name="resumen" required class="textarea-field"></textarea>
        
        <label for="imagen">Imagen:</label>
        <input type="file" name="imagen" accept="image/*" class="input-field">
        
        <label for="programa_id">Programa:</label>
        <select name="programa_id" required class="input-field">
            <?php foreach ($programas as $programa): ?>
                <option value="<?= htmlspecialchars($programa['nombre_unico']) ?>">
                    <?= htmlspecialchars($programa['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="categorias">Categorías:</label>
        <select name="categorias[]" multiple class="input-field" style="height: 100px;">
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= htmlspecialchars($categoria['nombre_unico']) ?>">
                    <?= htmlspecialchars($categoria['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" name="create_nota" class="submit-button">Crear Nota</button>
    </form>
</div>

<!-- Lista de notas -->
<div class="note-list">
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Resumen</th>
                <th>Imagen</th>
                <th>Programa</th>
                <th>Categorías</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notas as $nota): ?>
                <tr>
                    <td><?= htmlspecialchars($nota['titulo_unico']) ?></td>
                    <td><?= htmlspecialchars(substr($nota['resumen'], 0, 100)) ?>...</td>
                    <td>
                        <?php if ($nota['imagen']): ?>
                            <img src="<?= htmlspecialchars($nota['imagen']) ?>" alt="Imagen" width="50">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($nota['programa_nombre']) ?></td>
                    <td><?= htmlspecialchars($nota['categorias']) ?></td>
                    <td>
                        <a href="edit_nota.php?nota_id=<?= urlencode($nota['titulo_unico']) ?>" class="edit-button">Editar</a>
                        <a href="?delete_nota=<?= urlencode($nota['titulo_unico']) ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar esta nota?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>
