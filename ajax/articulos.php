<?php
require_once "../config/config.php";
require_once "../config/database.php";
require_once "../config/auth.php";

// Verificar autenticación
if (!Auth::check()) {
    echo '<div class="alert alert-danger">No autenticado</div>';
    exit;
}

$familia = intval($_GET['familia'] ?? 0);

if ($familia <= 0) {
    echo '<div class="alert alert-warning">Familia no válida</div>';
    exit;
}

$stmt = Database::execute(
    "SELECT idarticulos, articulo, pvp 
     FROM articulos 
     WHERE familia = ? 
     ORDER BY articulo",
    "i",
    [$familia]
);

if (!$stmt) {
    echo '<div class="alert alert-danger">Error al cargar artículos</div>';
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo '<div class="alert alert-info">No hay artículos en esta familia</div>';
    $stmt->close();
    exit;
}
?>

<div class="articulos-grid">
<?php while ($a = $result->fetch_object()): ?>
    <button class="btn btn-outline-primary articulo-btn w-100"
        onclick="addArticulo(
            <?= $a->idarticulos ?>,
            '<?= addslashes($a->articulo) ?>',
            <?= $a->pvp ?>
        )">
        <div class="articulo-nombre"><?= htmlspecialchars($a->articulo) ?></div>
        <div class="articulo-precio"><?= number_format($a->pvp, 2) ?> €</div>
    </button>
<?php endwhile; ?>
</div>


<?php $stmt->close(); ?>
