<?php
require_once "../config/database.php";
require_once "../config/auth.php";

// Requiere autenticación
Auth::requireRole('admin');

$db = Database::conectar();
$res = $db->query("SELECT * FROM familias WHERE eliminado='0' ORDER BY familia");
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
    <h3>📁 Familias de Artículos</h3>
    <a href="../index.php" class="btn btn-secondary">← Volver</a>
</div>

<a href="familias_form.php" class="btn btn-success mb-3">
    ➕ Nueva familia
</a>

<div class="card">
    <div class="card-body">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th width="80">ID</th>
                    <th>Familia</th>
                    <th width="200" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($res->num_rows == 0): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No hay familias registradas
                    </td>
                </tr>
            <?php endif; ?>
            
            <?php while($f = $res->fetch_object()): ?>
                <tr>
                    <td><?= $f->idfamilias ?></td>
                    <td><strong><?= htmlspecialchars($f->familia) ?></strong></td>
                    <td class="text-end">
                        <a href="familias_form.php?id=<?= $f->idfamilias ?>"
                           class="btn btn-sm btn-warning">
                            ✏️ Editar
                        </a>
                        <a href="familias_delete.php?id=<?= $f->idfamilias ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('⚠️ ¿Eliminar familia?\n\nEsto puede afectar a los artículos asociados.')">
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
