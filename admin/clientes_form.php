<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = $_GET['id'] ?? null;
$nombre = $telefono1 = $direccion = $email = "";

if ($id) {
    $c = $db->query("
        SELECT * FROM clientes WHERE idclientes=$id
    ")->fetch_object();
    $nombre = $c->nombre;
    $telefono1 = $c->telefono1;
    $direccion = $c->direccion;
    $email = $c->email;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Cliente</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-3">

<h3><?= $id ? "Editar" : "Nuevo" ?> cliente</h3>

<form method="post" action="clientes_save.php">
  <input type="hidden" name="id" value="<?= $id ?>">

  <div class="mb-2">
    <label>Nombre</label>
    <input type="text" name="nombre"
           class="form-control"
           required value="<?= $nombre ?>">
  </div>

  <div class="mb-2">
    <label>Teléfono</label>
    <input type="text" name="telefono1"
           class="form-control"
           value="<?= $telefono1 ?>">
  </div>

  <div class="mb-2">
    <label>Dirección</label>
    <input type="text" name="direccion"
           class="form-control"
           value="<?= $direccion ?>">
  </div>

  <div class="mb-2">
    <label>Email</label>
    <input type="email" name="email"
           class="form-control"
           value="<?= $email ?>">
  </div>

  <button class="btn btn-success">Guardar</button>
  <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
</form>

</body>
</html>
