<?php
require_once "../config/database.php";
$db = Database::conectar();

$fecha = $_GET['fecha'] ?? date("Y-m-d");

/* ¿Está cerrada la caja? */
$cierre = $db->query("
    SELECT * FROM cierres_caja WHERE fecha='$fecha'
")->fetch_object();

/* Resumen pedidos */
$res = $db->query("
    SELECT COUNT(*) pedidos, SUM(total) total
    FROM pedidos
    WHERE fecha='$fecha' AND cobrado='S'
")->fetch_object();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cierre de caja</title>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
</head>

<body class="bg-light">
<div class="container mt-4">

<h3>Caja diaria</h3>

<form class="row g-2 mb-3">
    <div class="col-4">
        <input type="date" name="fecha"
               value="<?= $fecha ?>"
               class="form-control">
    </div>
    <div class="col-2">
        <button class="btn btn-primary">Ver</button>
    </div>
</form>

<div class="card">
    <div class="card-body">
        <p><strong>Fecha:</strong> <?= $fecha ?></p>
        <p><strong>Pedidos cobrados:</strong> <?= $res->pedidos ?? 0 ?></p>
        <p><strong>Total ventas:</strong>
            <?= number_format($res->total ?? 0,2) ?> €
        </p>

        <?php if ($cierre): ?>
            <div class="alert alert-success">
                Caja cerrada el <?= $cierre->fecha_cierre ?>
            </div>
        <?php else: ?>
            <form method="post" action="cerrar.php"
                  onsubmit="return confirm('¿Cerrar caja del día?')">
                <input type="hidden" name="fecha" value="<?= $fecha ?>">
                <input type="hidden" name="total" value="<?= $res->total ?>">
                <input type="hidden" name="pedidos" value="<?= $res->pedidos ?>">
                <button class="btn btn-danger btn-lg w-100">
                    CERRAR CAJA
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<a href="../index.php" class="btn btn-secondary mt-3">Volver</a>

</div>
</body>
</html>
