<?php
require_once "../config/database.php";
$db = Database::conectar();
$res = $db->query("SELECT * FROM familias ORDER BY familia");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Familias</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-3">

<h3>Familias</h3>

<a href="familias_form.php" class="btn btn-success mb-2">
  + Nueva familia
</a>

<table class="table table-bordered">
<tr>
  <th>ID</th>
  <th>Familia</th>
  <th>Acciones</th>
</tr>

<?php while($f = $res->fetch_object()): ?>
<tr>
  <td><?= $f->idfamilias ?></td>
  <td><?= $f->familia ?></td>
  <td>
    <a href="familias_form.php?id=<?= $f->idfamilias ?>"
       class="btn btn-sm btn-warning">Editar</a>
    <a href="familias_delete.php?id=<?= $f->idfamilias ?>"
       class="btn btn-sm btn-danger"
       onclick="return confirm('¿Eliminar familia?')">Borrar</a>
  </td>
</tr>
<?php endwhile; ?>
</table>

<a href="../index.php" class="btn btn-secondary">Volver</a>

</body>
</html>
