<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = $_GET['id'] ?? null;
$familia = "";

if ($id) {
    $f = $db->query(
        "SELECT * FROM familias WHERE idfamilias=$id"
    )->fetch_object();
    $familia = $f->familia;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Familia</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-3">

<h3><?= $id ? "Editar" : "Nueva" ?> familia</h3>

<form method="post" action="familias_save.php">
  <input type="hidden" name="id" value="<?= $id ?>">

  <div class="mb-3">
    <label>Nombre</label>
    <input type="text" name="familia"
           class="form-control"
           required
           value="<?= $familia ?>">
  </div>

  <button class="btn btn-success">Guardar</button>
  <a href="familias.php" class="btn btn-secondary">Cancelar</a>
</form>

</body>
</html>
