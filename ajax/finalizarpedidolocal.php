<?php
session_start();
require_once "../config/database.php";

$db = Database::conectar();
$pedido_id = $_SESSION['pedido_id'] ?? null;

if (!$pedido_id) {
    echo json_encode(["ok" => false]);
    exit;
}
/* ───────────────── CALCULAR TOTAL PEDIDO ───────────────── */

$res = $db->query("
    SELECT SUM(cantidad * pvp) AS total
    FROM lineaspedido
    WHERE idpedidos = $pedido_id
");

if (!$res) {
    echo json_encode([
        "ok" => false,
        "error" => "Error SQL: " . $db->error
    ]);
    exit;
}

$row = $res->fetch_object();
$total = round(floatval($row->total), 2);

if ($total <= 0) {
    echo json_encode([
        "ok" => false,
        "error" => "El pedido no tiene líneas"
    ]);
    exit;
}

/* Marcar como pendiente */
$db->query("
    UPDATE pedidos
    SET servido='N', cobrado='N', domicilio='N', total = $total
    WHERE idpedidos=$pedido_id
");

/* Cerrar sesión del pedido */
unset($_SESSION['pedido_id']);

echo json_encode(["ok" => true]);
