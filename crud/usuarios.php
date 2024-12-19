<?php
include '../db_connection.php';
include '../db_connection.php';
$stmt = $pdo->query("SELECT * FROM vista_usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>Usuarios</h1>
<table>
    <tr>
        <th>Email</th>
        <th>Username</th>
        <th>Fecha Registro</th>
        <th>Avatar</th>
    </tr>
    <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?= $usuario['usuario_email'] ?></td>
            <td><?= $usuario['usuario_nombre'] ?></td>
            <td><?= $usuario['usuario_fecha_registro'] ?></td>
            <td><img src="<?= $usuario['usuario_avatar'] ?>" alt="Avatar" width="50"></td>
        </tr>
    <?php endforeach; ?>
</table>
