<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = $_GET['id'] ?? null;
$articulo = $pvp = $familia = "";

$fams = $db->query("SELECT * FROM familias ORDER BY familia");

if ($id) {
    $a = $db->query("
        SELECT * FROM articulos WHERE idarticulos=$id
    ")->fetch_object();
    $articulo = $a->articulo;
    $pvp = $a->pvp;
    $familia = $a->familia;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Artículo</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-3">

<h3><?= $id ? "Editar" : "Nuevo" ?> artículo</h3>

<form method="post" action="articulos_save.php">
  <input type="hidden" name="id" value="<?= $id ?>">

  <div class="mb-3">
    <label>Artículo</label>
    <input type="text" name="articulo"
           class="form-control"
           required
           value="<?= $articulo ?>">
  </div>

  <div class="mb-3">
    <label>Familia</label>
    <select name="familia" class="form-select" required>
      <option value="">-- Seleccionar --</option>
      <?php while($f = $fams->fetch_object()): ?>
        <option value="<?= $f->idfamilias ?>"
          <?= $familia == $f->idfamilias ? "selected" : "" ?>>
          <?= $f->familia ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label>PVP (€)</label>
    <input type="number" step="0.01" name="pvp"
           class="form-control"
           required
           value="<?= $pvp ?>">
  </div>

  <button class="btn btn-success">Guardar</button>
  <a href="articulos.php" class="btn btn-secondary">Cancelar</a>
</form>

</body>
</html>
