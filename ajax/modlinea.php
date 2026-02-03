<?php
session_start();
require_once "../config/database.php";

$db = Database::conectar();

$id = intval($_POST['id']);
$accion = $_POST['accion'];
$pedido_id = $_SESSION['pedido_id'];

if ($accion == "sumar") {
    $db->query("UPDATE lineaspedido SET cantidad = cantidad + 1 WHERE idlineasPedido=$id");
}

if ($accion == "restar") {
    $db->query("UPDATE lineaspedido SET cantidad = cantidad - 1 WHERE idlineasPedido=$id");
    $db->query("DELETE FROM lineaspedido WHERE cantidad <= 0");
}

/* Recargar pedido */
$res = $db->query("SELECT * FROM lineaspedido WHERE idpedidos=$pedido_id");

$total = 0;
$html = "";

while ($l = $res->fetch_object()) {
    $lineaTotal = $l->cantidad * $l->pvp;
    $total += $lineaTotal;

    $html .= "
    <div class='d-flex justify-content-between align-items-center mb-1'>
        <div>
            <strong>$l->articulo</strong><br>
            <small>{$l->cantidad} x " . number_format($l->pvp,2) . " €</small>
        </div>
        <div>
            <button class='btn btn-sm btn-outline-secondary'
                onclick='restarLinea($l->idlineasPedido)'>−</button>
            <button class='btn btn-sm btn-outline-secondary'
                onclick='sumarLinea($l->idlineasPedido)'>+</button>
        </div>
        <div class='fw-bold'>
            " . number_format($lineaTotal,2) . " €
        </div>
    </div>";
}

echo json_encode([
    "lineas" => $html,
    "total" => number_format($total, 2)
]);
