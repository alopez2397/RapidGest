<?php
session_start();
require_once "../config/database.php";

$db = Database::conectar();
$pedido_id = $_SESSION['pedido_id'] ?? 0;

if (!$pedido_id) {
    echo json_encode(["ok" => false]);
    exit;
}

/* DATOS CLIENTE */
$pedido = $db->query("
    SELECT c.telefono, c.nombre, c.direccion
    FROM pedidos p
    JOIN clientes c ON c.idclientes = p.idclientes
    WHERE p.idpedidos = $pedido_id
")->fetch_object();

/* LÍNEAS */
$res = $db->query("
    SELECT articulo, cantidad, (cantidad*pvp) total
    FROM lineaspedido
    WHERE idpedidos = $pedido_id
");

$lineas = [];
$total = 0;

while ($l = $res->fetch_object()) {
    $total += $l->total;
    $lineas[] = [
        "articulo" => $l->articulo,
        "cantidad" => (int)$l->cantidad,
        "total"    => (float)$l->total
    ];
}

/* RESPUESTA */
echo json_encode([
    "pedido"  => $pedido_id,
    "cliente" => $pedido,
    "lineas"  => $lineas,
    "total"   => round($total, 2)
]);
