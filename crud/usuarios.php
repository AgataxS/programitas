<?php
include('../includes/header.php');
include('../db_connection.php');

// Determinar la acción
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Crear usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'create') {
    // Obtener el correo del formulario
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $avatar = null;

    // Verificar si el correo ya existe
    $sql_check = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(['email' => $email]);
    $email_exists = $stmt_check->fetchColumn();

    if ($email_exists) {
        echo "Error: El correo electrónico ya está registrado.";
        exit();
    }

    // Manejo de avatar (si se sube un archivo)
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $target_dir = "../uploads/usuarios/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $avatar = $target_dir . uniqid() . '_' . basename($_FILES['avatar']['name']);
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
    }

    // Insertar usuario en la base de datos
    $sql = "INSERT INTO usuarios (email, username, password, avatar) VALUES (:email, :username, :password, :avatar)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email,
        'username' => $username,
        'password' => $password,
        'avatar' => $avatar
    ]);

    header("Location: usuarios.php");
    exit();
}

// Editar usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $avatar = $_POST['avatar_actual'];

    // Manejo de nuevo avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $target_dir = "../uploads/usuarios/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $avatar = $target_dir . uniqid() . '_' . basename($_FILES['avatar']['name']);
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
    }

    $sql = "UPDATE usuarios SET username = :username, avatar = :avatar WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username, 'avatar' => $avatar, 'email' => $email]);

    header("Location: usuarios.php");
    exit();
}

// Eliminar usuario
if ($action == 'delete' && isset($_GET['email'])) {
    $email = $_GET['email'];
    $sql = "DELETE FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);

    header("Location: usuarios.php");
    exit();
}

// Obtener usuarios
$sql = "SELECT * FROM usuarios";
$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll();
?>

<!-- Mostrar lista de usuarios -->
<?php if ($action == 'list'): ?>
    <h1>Gestionar Usuarios</h1>
    <a href="usuarios.php?action=create" class="submit-button">Nuevo Usuario</a>
    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>Username</th>
                <th>Avatar</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= htmlspecialchars($usuario['username']) ?></td>
                    <td>
                        <?php if ($usuario['avatar']): ?>
                            <img src="<?= htmlspecialchars($usuario['avatar']) ?>" alt="Avatar" width="50">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="usuarios.php?action=edit&email=<?= $usuario['email'] ?>" class="edit-button">Editar</a>
                        <a href="usuarios.php?action=delete&email=<?= $usuario['email'] ?>" class="delete-button" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Formulario para crear o editar -->
<?php if ($action == 'create' || $action == 'edit'): ?>
    <?php
    $usuario = ['email' => '', 'username' => '', 'avatar' => ''];
    if ($action == 'edit' && isset($_GET['email'])) {
        $email = $_GET['email'];
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
    }
    ?>
    <h1><?= $action == 'create' ? 'Crear Usuario' : 'Editar Usuario' ?></h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">
        
        <!-- Campo de correo electrónico -->
        <label for="email">Correo Electrónico:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required <?= $action == 'edit' ? 'readonly' : '' ?>>
        
        <label for="username">Nombre de usuario:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($usuario['username']) ?>" required>
        
        <label for="password">Contraseña:</label>
        <input type="password" name="password" <?= $action == 'edit' ? '' : 'required' ?>>
        
        <label for="avatar">Avatar:</label>
        <?php if ($usuario['avatar']): ?>
            <img src="<?= htmlspecialchars($usuario['avatar']) ?>" alt="Avatar actual" style="max-width: 200px;">
            <input type="hidden" name="avatar_actual" value="<?= htmlspecialchars($usuario['avatar']) ?>">
        <?php endif; ?>
        <input type="file" name="avatar" accept="image/*">
        
        <button type="submit" class="submit-button"><?= $action == 'create' ? 'Crear' : 'Actualizar' ?></button>
    </form>
<?php endif; ?>

<?php include('../includes/footer.php'); ?>
