<?php
require_once "../config/database.php";
$db = Database::conectar();

$res = $db->query("
    SELECT a.idarticulos, a.articulo, a.pvp, f.familia
    FROM articulos a
    LEFT JOIN familias f ON a.familia = f.idfamilias
    ORDER BY a.articulo
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Artículos</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-3">

<h3>Artículos</h3>

<a href="articulos_form.php" class="btn btn-success mb-2">
  + Nuevo artículo
</a>

<table class="table table-bordered table-striped">
<tr>
  <th>ID</th>
  <th>Artículo</th>
  <th>Familia</th>
  <th>PVP</th>
  <th>Acciones</th>
</tr>

<?php while($a = $res->fetch_object()): ?>
<tr>
  <td><?= $a->idarticulos ?></td>
  <td><?= $a->articulo ?></td>
  <td><?= $a->familia ?></td>
  <td><?= number_format($a->pvp,2) ?> €</td>
  <td>
    <a href="articulos_form.php?id=<?= $a->idarticulos ?>"
       class="btn btn-sm btn-warning">Editar</a>
    <a href="articulos_delete.php?id=<?= $a->idarticulos ?>"
       class="btn btn-sm btn-danger"
       onclick="return confirm('¿Eliminar artículo?')">Borrar</a>
  </td>
</tr>
<?php endwhile; ?>
</table>

<a href="../index.php" class="btn btn-secondary">Volver</a>

</body>
</html>
