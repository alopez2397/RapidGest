<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = intval($_GET['id']);
$res = $db->query("SELECT * FROM articulos WHERE idfamilias=$id");
?>
<div class="row">
<?php while ($a = $res->fetch_object()): ?>
<div class="col-4 p-1">
    <button class="btn btn-outline-primary articulo-btn w-100"
        onclick="addArticulo(
            <?= $a->idarticulos ?>,
            '<?= addslashes($a->articulo) ?>',
            <?= $a->pvp ?>
        )">
        <div class="articulo-nombre"><?= $a->articulo ?></div>
        <div class="articulo-precio"><?= number_format($a->pvp,2) ?> €</div>
    </button>
</div>
<?php endwhile; ?>
</div>
