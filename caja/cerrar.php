<?php
require_once "../config/database.php";
$db = Database::conectar();

$fecha    = $_POST['fecha'];
$total    = $_POST['total'];
$pedidos  = $_POST['pedidos'];
$empleado = "Sistema"; // luego puedes usar sesión

/* Evitar doble cierre */
$existe = $db->query("
    SELECT id FROM cierres_caja WHERE fecha='$fecha'
");
if ($existe->num_rows > 0) {
    header("Location: resumen.php?fecha=$fecha");
    exit;
}

/* Insertar cierre */
$db->query("
    INSERT INTO cierres_caja
    (fecha, pedidos, total, efectivo, fecha_cierre, empleado)
    VALUES
    ('$fecha', $pedidos, $total, $total, NOW(), '$empleado')
");

header("Location: resumen.php?fecha=$fecha");
