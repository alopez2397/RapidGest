<?php
require_once "../config/database.php";
require_once "../config/auth.php";

// Requiere autenticación
Auth::require();

$db = Database::conectar();

$res = $db->query("SELECT * FROM clientes ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Familias - RapidGest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-3">

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📁 Clientes</h3>
    <a href="../index.php" class="btn btn-secondary">← Volver</a>
</div>

<a href="clientes_form.php" class="btn btn-success mb-3">
    ➕ Nuevo cliente
</a>
<div class="card">
    <div class="card-body">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th width="80">ID</th>
                    <th >Cliente</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th width="200" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($res->num_rows == 0): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No hay clientes registrados
                    </td>
                </tr>
            <?php endif; ?>
            
            <?php while($f = $res->fetch_object()): ?>
                <tr>
                    <td><?= $f->idclientes ?></td>
                    <td><strong><?= htmlspecialchars($f->nombre) ?></strong></td>
                    <td><strong><?= htmlspecialchars($f->telefono1) ?></strong></td>
                    <td><strong><?= htmlspecialchars($f->email) ?></strong></td>
                    <td class="text-end">
                        <a href="clientes_form.php?id=<?= $f->idclientes ?>"
                           class="btn btn-sm btn-warning">
                            ✏️ Editar
                        </a>
                        <a href="clientes_delete.php?id=<?= $f->idclientes ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('⚠️ ¿Eliminar cliente?\n\n')">
                            🗑️ Borrar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

</body>
</html>
