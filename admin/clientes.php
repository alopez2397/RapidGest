<?php
require_once "../config/database.php";
$db = Database::conectar();
$res = $db->query("SELECT * FROM clientes ORDER BY nombre");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Clientes</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-3">

<h3>Clientes</h3>

<a href="clientes_form.php" class="btn btn-success mb-2">
  + Nuevo cliente
</a>

<table class="table table-bordered">
<tr>
  <th>Nombre</th>
  <th>Teléfono</th>
  <th>Email</th>
  <th>Acciones</th>
</tr>

<?php while($c = $res->fetch_object()): ?>
<tr>
  <td><?= $c->nombre ?></td>
  <td><?= $c->telefono1 ?></td>
  <td><?= $c->email ?></td>
  <td>
    <a href="clientes_form.php?id=<?= $c->idclientes ?>"
       class="btn btn-sm btn-warning">Editar</a>
    <a href="clientes_delete.php?id=<?= $c->idclientes ?>"
       class="btn btn-sm btn-danger"
       onclick="return confirm('¿Eliminar cliente?')">Borrar</a>
  </td>
</tr>
<?php endwhile; ?>
</table>

<a href="../index.php" class="btn btn-secondary">Volver</a>

</body>
</html>
