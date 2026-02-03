<?php
session_start();
require_once "../config/database.php";

header("Content-Type: application/json");
error_reporting(0);

$db = Database::conectar();
$pedido_id = $_SESSION['pedido_id'] ?? 0;

if (!$pedido_id) {
    echo json_encode(["lineas" => "", "total" => "0.00"]);
    exit;
}

$res = $db->query("
    SELECT articulo, cantidad, pvp
    FROM lineaspedido
    WHERE idpedidos = $pedido_id
");

if (!$res) {
    echo json_encode(["lineas" => "", "total" => "0.00"]);
    exit;
}

$total = 0;
$html = "";

while ($l = $res->fetch_object()) {
    $lineaTotal = $l->cantidad * $l->pvp;
    $total += $lineaTotal;

    $html .= "
    <div class='d-flex justify-content-between mb-1'>
        <div>
            <strong>{$l->articulo}</strong><br>
            <small>{$l->cantidad} x " . number_format($l->pvp,2) . " €</small>
        </div>
        <div class='fw-bold'>" . number_format($lineaTotal,2) . " €</div>
    </div>";
}

echo json_encode([
    "lineas" => $html,
    "total" => number_format($total,2)
]);
