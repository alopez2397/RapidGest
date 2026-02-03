<?php
require_once "config/database.php";
$db = Database::conectar();
$id = intval($_GET['id']);

$lineas = $db->query("
    SELECT articulo, cantidad
    FROM lineaspedido
    WHERE idpedidos=$id
");
?>
<h3>Pedido #<?= $id ?></h3>
<ul>
<?php while ($l = $lineas->fetch_object()): ?>
    <li><?= $l->cantidad ?> x <?= $l->articulo ?></li>
<?php endwhile; ?>
</ul>
