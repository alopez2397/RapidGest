<?php
require_once "../config/database.php";
$db = Database::conectar();

$resumen = $db->query("
    SELECT 
        fecha,
        COUNT(*) AS pedidos,
        SUM(total) AS total
    FROM pedidos
    WHERE cobrado = 'S'
    OR servido = 'S'
    GROUP BY fecha
    ORDER BY fecha DESC
    LIMIT 7
");


?>

<!DOCTYPE html>
<html>
<head>
    <title>Resúmen diario</title>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
</head>

<body class="bg-light">
<div class="container mt-4">

<h3>Resúmen de ventas</h3>

<!-- RESUMEN POR FECHAS -->
<div class="card mt-3">
    <div class="card-header bg-success text-white">
        Resumen de ventas (últimos días)
    </div>

    <div class="card-body p-0">
        <table class="table table-striped mb-0 text-center">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Pedidos</th>
                    <th>Total €</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($resumen && $resumen->num_rows > 0): ?>
                <?php while ($r = $resumen->fetch_object()): ?>
                <tr>
                    <td><?= date("d/m/Y", strtotime($r->fecha)) ?></td>
                    <td><?= $r->pedidos ?></td>
                    <td><strong><?= number_format($r->total, 2) ?> €</strong></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Sin datos</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<a href="../index.php" class="btn btn-secondary mt-3">Volver</a>
</div>
</body>
</html>




